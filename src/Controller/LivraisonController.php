<?php

namespace App\Controller;

use App\Entity\Livraison;
use App\Entity\Colis;
use App\Repository\ColisRepository;
use App\Repository\LivraisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UtilisateurRepository;
use App\Form\TerminerLivraisonType; 
use Symfony\Component\HttpFoundation\Request;
use App\Service\NotificationService;

#[Route('/livraison')]
class LivraisonController extends AbstractController
{
    #[Route('/', name: 'app_livraison_index', methods: ['GET'])]
    public function index(ColisRepository $colisRepository): Response
    {
        // colis dispo sans liv
        $colisDisponibles = $colisRepository->findColisSansLivraison();

        return $this->render('front/livraison/index.html.twig', [
            'colis_disponibles' => $colisDisponibles,
        ]);
    }

    #[Route('/mes-livraisons', name: 'app_livraison_mes_livraisons', methods: ['GET'])]
    public function mesLivraisons(LivraisonRepository $livraisonRepository): Response
    {
        // Pour l'instant, afficher toutes les livraisons
        // Plus tard : $livraisonRepository->findByLivreur($this->getUser()->getId())
        $livraisons = $livraisonRepository->findAll();

        return $this->render('front/livraison/mes_livraisons.html.twig', [
            'livraisons' => $livraisons,
        ]);
    }

    #[Route('/prendre/{id}', name: 'app_livraison_prendre', methods: ['POST'])]
    public function prendreEnCharge(int $id, ColisRepository $colisRepository, EntityManagerInterface $entityManager,  UtilisateurRepository $userRepo, NotificationService $notificationService): Response
    {
        $colis = $colisRepository->find($id);

        if (!$colis) {
            $this->addFlash('error', 'Colis introuvable.');
            return $this->redirectToRoute('app_livraison_index');
        }

        // Verification colis n'est pas deja priste
        if (!$colis->getLivraisons()->isEmpty()) {
            $this->addFlash('error', 'Ce colis est déjà pris en charge.');
            return $this->redirectToRoute('app_livraison_index');
        }

        //temp : prendre le premier livreur disponible pou le test
        $livreur = $userRepo->findOneBy(['role' => 'Livreur']);
    
        if (!$livreur) {
            $this->addFlash('error', 'Aucun livreur trouvé dans la base de données.');
            return $this->redirectToRoute('app_livraison_index');
        }
        //end temp
        
        $livraison = new Livraison();
        $livraison->setColis($colis);
        // Plus tard remplacer par l'utilisateur connecté
        // $livraison->setLivreur($this->getUser());
        $livraison->setLivreur($livreur);
        
        $montant = $colis->calculerMontant();
        $livraison->setTotal($montant);
        
        $ancienStatut = $colis->getStatut();
        $colis->setStatut('en_cours');
        $colis->setDateExpedition(new \DateTime());

        $entityManager->persist($livraison);
        $entityManager->flush();

        $notificationService->notifierChangementStatut($colis, $ancienStatut, 'en_cours');

        $this->addFlash('success', 'Colis pris en charge avec succès ! Montant : ' . number_format($montant, 2, ',', ' ') . ' €');

        return $this->redirectToRoute('app_livraison_mes_livraisons');
    }

    #[Route('/terminer/{id}', name: 'app_livraison_terminer', methods: ['GET', 'POST'])]
    public function terminer(Request $request, Livraison $livraison, EntityManagerInterface $entityManager, NotificationService $notificationService ): Response
    {
        $form = $this->createForm(TerminerLivraisonType::class, $livraison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $livraison->setStatut('termine');
            $livraison->setDateFin(new \DateTime());
            $ancienStatut = $livraison->getColis()->getStatut();
            $livraison->getColis()->setStatut('livre');

            $entityManager->flush();

            $notificationService->notifierChangementStatut($livraison->getColis(), $ancienStatut, 'livre');

            $total = $livraison->getTotal();
            $this->addFlash('success', 'Livraison terminée avec succès ! Montant : ' . number_format($total, 2, ',', ' ') . ' €');
            return $this->redirectToRoute('app_livraison_mes_livraisons');
        }

        return $this->render('front/livraison/terminer.html.twig', [
            'livraison' => $livraison,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/colis/{id}/details', name: 'app_livraison_colis_details', methods: ['GET'])]
    public function detailsColis(Colis $colis): Response
    {
        // Vérif colis dispo
        if (!$colis->estDisponible()) {
            $this->addFlash('error', 'Ce colis n\'est plus disponible.');
            return $this->redirectToRoute('app_livraison_index');
        }
        
        return $this->render('front/livraison/details_colis.html.twig', [
            'colis' => $colis,
        ]);
    }

    //sans prendre en charge
    #[Route('/details/{id}', name: 'app_livraison_details', methods: ['GET'])]
    public function details(Livraison $livraison): Response
    {
        return $this->render('front/livraison/details_colis_simple.html.twig', [
            'livraison' => $livraison,
        ]);
    }
}