<?php

namespace App\Controller;

use App\Entity\Colis;
use App\Form\ColisType;
use App\Repository\ColisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UtilisateursRepository;
use App\Service\QrCodeService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/colis')]
#[IsGranted('ROLE_ENTREPRISE')]
class ColisController extends AbstractController
{
    #[Route('/', name: 'app_colis_index', methods: ['GET'])]
    public function index(ColisRepository $colisRepository): Response
    {
        // Filtrer les colis par l'utilisateur connecté (expéditeur)
        $colis = $colisRepository->findBy(['expediteur' => $this->getUser()]);

        return $this->render('front/colis/index.html.twig', [
            'colis' => $colis,
        ]);
    }

    
    #[Route('/new', name: 'app_colis_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UtilisateursRepository $userRepo,
        QrCodeService $qrCodeService,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        $colis = new Colis();
        $colis->setExpediteur($this->getUser());

        $form = $this->createForm(ColisType::class, $colis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantCalcule = $colis->calculerMontant();

            // ✅ 1er flush → pour générer l'ID
            $entityManager->persist($colis);
            $entityManager->flush();

            // ✅ Génération du QR Code avec l'URL de la page show
            $contenu = sprintf(
                "📦 PACKTRACK - Colis #%d\nStatut: %s\nDestination: %s\nDestinataire: %s %s\nPoids: %s kg\nMontant: %s €",
                $colis->getId(),
                $colis->getStatut(),
                $colis->getAdresseDestination(),
                $colis->getDestinataire()->getPrenom(),
                $colis->getDestinataire()->getNom(),
                $colis->getPoids(),
                number_format($colis->calculerMontant(), 2)
            );

            $colis->setQrCode($qrCodeService->generate($contenu));

            // ✅ 2ème flush → pour sauvegarder le QR code
            $entityManager->flush();

            $this->addFlash('success', 'Colis créé avec succès ! Montant à payer : ' . number_format($montantCalcule, 2, ',', ' ') . ' €');

            return $this->redirectToRoute('app_colis_index');
        }

        return $this->render('front/colis/new.html.twig', [
            'colis' => $colis,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_colis_show', methods: ['GET'])]
    public function show(Colis $colis): Response
    {
        if ($colis->getExpediteur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce colis.');
        }

        return $this->render('front/colis/show.html.twig', [
            'colis' => $colis,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_colis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Colis $colis, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur connecté est l'expéditeur du colis
        if ($colis->getExpediteur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce colis.');
        }
        
        $form = $this->createForm(ColisType::class, $colis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantCalcule = $colis->calculerMontant();
            
            $entityManager->flush();

            $this->addFlash('success', 'Colis modifié avec succès ! Montant à payer : ' . number_format($montantCalcule, 2, ',', ' ') . ' €');

            return $this->redirectToRoute('app_colis_index');
        }

        return $this->render('front/colis/edit.html.twig', [
            'colis' => $colis,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_colis_delete', methods: ['POST'])]
    public function delete(Request $request, Colis $colis, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur connecté est l'expéditeur du colis
        if ($colis->getExpediteur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce colis.');
        }

        if ($this->isCsrfTokenValid('delete'.$colis->getId(), $request->request->get('_token'))) {
            $entityManager->remove($colis);
            $entityManager->flush();

            $this->addFlash('success', 'Colis supprimé avec succès !');
        }

        return $this->redirectToRoute('app_colis_index');
    }









}