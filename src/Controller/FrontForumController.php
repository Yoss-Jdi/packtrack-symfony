<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Publication;
use App\Entity\PublicationReaction;
use App\Entity\Utilisateurs;
use App\Form\CommentaireType;
use App\Form\FrontPublicationType;
use App\Repository\CommentaireRepository;
use App\Repository\PublicationReactionRepository;
use App\Repository\PublicationRepository;
use App\Service\PostSummarizer;
use App\Service\ChatbotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class FrontForumController extends AbstractController
{
    #[Route('/community/chatbot', name: 'front_forum_chatbot', methods: ['POST'])]
    public function chatbot(Request $request, ChatbotService $chatbotService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';
        $history = $data['history'] ?? [];

        if (empty($message)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Message is required'
            ], 400);
        }

        $response = $chatbotService->chat($message, $history);

        return new JsonResponse($response);
    }

    #[Route('/community', name: 'front_forum_index', methods: ['GET'])]
    public function index(PublicationRepository $publicationRepository, PublicationReactionRepository $reactionRepository): Response
    {
        $publications = $publicationRepository->findLatestActive(30);

        $countsById = [];
        foreach ($publications as $p) {
            $countsById[$p->getId()] = $reactionRepository->getCounts($p);
        }

        return $this->render('front/forum/index.html.twig', [
            'publications' => $publications,
            'countsById' => $countsById,
        ]);
    }

    #[Route('/community/new', name: 'front_forum_publication_new', methods: ['GET', 'POST'])]
    public function newPublication(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $publication = new Publication();

        /** @var Utilisateurs|null $user */
        $user = $this->getUser();
        if ($user instanceof Utilisateurs) {
            $publication->setAuteur($user);
        }

        $publication->setStatut('active');

        $form = $this->createForm(FrontPublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($publication);
            $em->flush();

            $this->addFlash('success', 'Publication créée avec succès.');

            return $this->redirectToRoute('front_forum_show', [
                'id' => $publication->getId(),
            ]);
        }

        return $this->render('front/forum/publication_form.html.twig', [
            'mode' => 'create',
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/community/{id}/edit', name: 'front_forum_publication_edit', methods: ['GET', 'POST'])]
    public function editPublication(Publication $publication, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Utilisateurs|null $user */
        $user = $this->getUser();

        if (!$user instanceof Utilisateurs) {
            throw $this->createAccessDeniedException();
        }

        if ($publication->getAuteur()?->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres publications.');
        }   

        $form = $this->createForm(FrontPublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Publication modifiée avec succès.');

            return $this->redirectToRoute('front_forum_show', [
                'id' => $publication->getId(),
            ]);
        }

        return $this->render('front/forum/publication_form.html.twig', [
            'mode' => 'edit',
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/community/{id}/delete', name: 'front_forum_publication_delete', methods: ['POST'])]
    public function deletePublication(Publication $publication, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Utilisateurs|null $user */
        $user = $this->getUser();

        if (!$user instanceof Utilisateurs) {
            throw $this->createAccessDeniedException();
        }

        if ($publication->getAuteur()?->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres publications.');
        }

        if (!$this->isCsrfTokenValid('delete_publication_'.$publication->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $em->remove($publication);
        $em->flush();

        $this->addFlash('success', 'Publication supprimée.');

        return $this->redirectToRoute('front_forum_index');
    }

    #[Route('/community/{id}', name: 'front_forum_show', methods: ['GET', 'POST'])]
    public function show(
        Publication $publication,
        Request $request,
        EntityManagerInterface $em,
        CommentaireRepository $commentaireRepository,
        PublicationReactionRepository $reactionRepository,
        PostSummarizer $summarizer
    ): Response {
        if (!$publication->isActive() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createNotFoundException();
        }

        $counts = $reactionRepository->getCounts($publication);
        $commentaires = $commentaireRepository->findForPublication($publication);

        // Generate AI summary
        $aiSummary = null;
        if ($publication->getContenu()) {
            try {
                $aiSummary = $summarizer->summarize(
                    $publication->getTitre(),
                    $publication->getContenu(),
                    count($commentaires)
                );
            } catch (\Exception $e) {
                // Silently fail if AI summary generation fails
            }
        }

        $userReaction = null;
        $user = $this->getUser();
        if ($user instanceof Utilisateurs) {
            $existing = $reactionRepository->findOneForUser($publication, $user);
            if ($existing !== null) {
                $userReaction = $existing->getReaction();
            }
        }

        $commentFormView = null;
        if ($user instanceof Utilisateurs) {
            $commentaire = new Commentaire();
            $commentaire->setPublication($publication);
            $commentaire->setAuteur($user);

            $form = $this->createForm(CommentaireType::class, $commentaire);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($commentaire);
                $em->flush();

                $this->addFlash('success', 'Comment added.');
                return $this->redirectToRoute('front_forum_show', ['id' => $publication->getId()]);
            }

            $commentFormView = $form->createView();
        }

        return $this->render('front/forum/show.html.twig', [
            'publication' => $publication,
            'commentaires' => $commentaires,
            'comment_form' => $commentFormView,
            'counts' => $counts,
            'user_reaction' => $userReaction,
            'ai_summary' => $aiSummary,
        ]);
    }

    #[Route('/community/{id}/react', name: 'front_forum_react', methods: ['POST'])]
    public function react(
        Publication $publication,
        Request $request,
        EntityManagerInterface $em,
        PublicationReactionRepository $reactionRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Utilisateurs $user */
        $user = $this->getUser();

        if (!$publication->isActive() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('react_'.$publication->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $reaction = (int) $request->request->get('reaction');
        if (!in_array($reaction, [PublicationReaction::LIKE, PublicationReaction::DISLIKE], true)) {
            throw $this->createNotFoundException();
        }

        $existing = $reactionRepository->findOneForUser($publication, $user);
        if ($existing === null) {
            $existing = new PublicationReaction();
            $existing->setPublication($publication);
            $existing->setAuteur($user);
            $existing->setReaction($reaction);
            $em->persist($existing);
        } else {
            if ($existing->getReaction() === $reaction) {
                $em->remove($existing);
            } else {
                $existing->setReaction($reaction);
            }
        }

        $em->flush();

        return $this->redirectToRoute('front_forum_show', ['id' => $publication->getId()]);
    }

    #[Route('/community/comment/{id}/edit', name: 'front_forum_comment_edit', methods: ['GET', 'POST'])]
    public function editComment(Commentaire $commentaire, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Utilisateurs|null $user */
        $user = $this->getUser();

        if (!$user instanceof Utilisateurs) {
            throw $this->createAccessDeniedException();
        }

        if ($commentaire->getAuteur()?->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres commentaires.');
        }

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Commentaire modifié avec succès.');

            return $this->redirectToRoute('front_forum_show', [
                'id' => $commentaire->getPublication()->getId(),
            ]);
        }

        return $this->render('front/forum/comment_edit.html.twig', [
            'publication' => $commentaire->getPublication(),
            'commentaire' => $commentaire,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/community/comment/{id}/delete', name: 'front_forum_comment_delete', methods: ['POST'])]
    public function deleteComment(Commentaire $commentaire, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Utilisateurs|null $user */
        $user = $this->getUser();

        if (!$user instanceof Utilisateurs) {
            throw $this->createAccessDeniedException();
        }

        if ($commentaire->getAuteur()?->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres commentaires.');
        }

        if (!$this->isCsrfTokenValid('delete_comment_'.$commentaire->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $publicationId = $commentaire->getPublication()->getId();

        $em->remove($commentaire);
        $em->flush();

        $this->addFlash('success', 'Commentaire supprimé.');

        return $this->redirectToRoute('front_forum_show', [
            'id' => $publicationId,
        ]);
    }
}