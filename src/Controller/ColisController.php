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
use App\Repository\UtilisateurRepository;

#[Route('/colis')]
class ColisController extends AbstractController
{
    #[Route('/', name: 'app_colis_index', methods: ['GET'])]
    public function index(ColisRepository $colisRepository): Response
    {
        // Pour l'instant, on affiche tous les colis
        // Plus tard, filtrerez par utilisateur connecté
        $colis = $colisRepository->findAll();

        return $this->render('colis/index.html.twig', [
            'colis' => $colis,
        ]);
    }

    #[Route('/new', name: 'app_colis_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UtilisateurRepository $userRepo): Response
    {
        $colis = new Colis();
        
        // IMPORTANT : Assigner l'expéditeur AVANT de créer le formulaire
        // TEMPORAIRE : Prendre le premier utilisateur "Entreprise"
        $entreprise = $userRepo->findOneBy(['role' => 'Entreprise']);
        
        if (!$entreprise) {
            $this->addFlash('error', 'Aucune entreprise trouvée dans la base de données.');
            return $this->redirectToRoute('app_colis_index');
        }
        
        // Plus tard, remplacer par l'utilisateur connecté
        // $colis->setExpediteur($this->getUser());
        $colis->setExpediteur($entreprise);
        
        // Maintenant créer le formulaire avec l'expéditeur déjà assigné
        $form = $this->createForm(ColisType::class, $colis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantCalcule = $colis->calculerMontant();
        
            $entityManager->persist($colis);
            $entityManager->flush();

            $this->addFlash('success', 'Colis créé avec succès ! Montant à payer : ' . number_format($montantCalcule, 2, ',', ' ') . ' €');

            return $this->redirectToRoute('app_colis_index');
        }

        return $this->render('colis/new.html.twig', [
            'colis' => $colis,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_colis_show', methods: ['GET'])]
    public function show(Colis $colis): Response
    {
        return $this->render('colis/show.html.twig', [
            'colis' => $colis,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_colis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Colis $colis, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ColisType::class, $colis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Colis modifié avec succès !');

            return $this->redirectToRoute('app_colis_index');
        }

        return $this->render('colis/edit.html.twig', [
            'colis' => $colis,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_colis_delete', methods: ['POST'])]
    public function delete(Request $request, Colis $colis, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$colis->getId(), $request->request->get('_token'))) {
            $entityManager->remove($colis);
            $entityManager->flush();

            $this->addFlash('success', 'Colis supprimé avec succès !');
        }

        return $this->redirectToRoute('app_colis_index');
    }
}