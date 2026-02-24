<?php

namespace App\Controller\Admin;

use App\Entity\Publication;
use App\Entity\Utilisateurs;
use App\Form\PublicationType;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/forum/publications')]
class AdminPublicationController extends AbstractController
{
    #[Route('', name: 'admin_forum_publication_index', methods: ['GET'])]
    public function index(PublicationRepository $publicationRepository): Response
    {
        return $this->render('admin/forum/publications/index.html.twig', [
            'publications' => $publicationRepository->findBy([], ['datePublication' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'admin_forum_publication_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $publication = new Publication();

        /** @var Utilisateurs $user */
        $user = $this->getUser();
        if ($user instanceof Utilisateurs) {
            $publication->setAuteur($user);
        }

        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($publication);
            $em->flush();

            $this->addFlash('success', 'Publication créée avec succès.');
            return $this->redirectToRoute('admin_forum_publication_index');
        }

        return $this->render('admin/forum/publications/form.html.twig', [
            'mode' => 'create',
            'publication' => $publication,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_forum_publication_edit', methods: ['GET', 'POST'])]
    public function edit(Publication $publication, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Publication modifiée avec succès.');
            return $this->redirectToRoute('admin_forum_publication_index');
        }

        return $this->render('admin/forum/publications/form.html.twig', [
            'mode' => 'edit',
            'publication' => $publication,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_forum_publication_delete', methods: ['POST'])]
    public function delete(Publication $publication, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_publication_'.$publication->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $em->remove($publication);
        $em->flush();

        $this->addFlash('success', 'Publication supprimée.');
        return $this->redirectToRoute('admin_forum_publication_index');
    }
}