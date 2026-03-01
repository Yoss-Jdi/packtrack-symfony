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
use App\Repository\UtilisateursRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Service\NotificationService;
use App\Service\PredictionService;          // ← ajouter
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/livraison')]
#[IsGranted('ROLE_LIVREUR')]
class LivraisonController extends AbstractController
{
    #[Route('/', name: 'app_livraison_index', methods: ['GET'])]
    public function index(ColisRepository $colisRepository): Response
    {
        $colisDisponibles = $colisRepository->findColisSansLivraison();
        return $this->render('front/livraison/index.html.twig', [
            'colis_disponibles' => $colisDisponibles,
        ]);
    }

    #[Route('/mes-livraisons', name: 'app_livraison_mes_livraisons', methods: ['GET'])]
    public function mesLivraisons(LivraisonRepository $livraisonRepository): Response
    {
        $livraisons = $livraisonRepository->findBy(['livreur' => $this->getUser()]);
        return $this->render('front/livraison/mes_livraisons.html.twig', [
            'livraisons' => $livraisons,
        ]);
    }

    #[Route('/prendre/{id}', name: 'app_livraison_prendre', methods: ['POST'])]
    public function prendreEnCharge(
        int $id,
        ColisRepository $colisRepository,
        EntityManagerInterface $entityManager,
        NotificationService $notificationService,
        PredictionService $predictionService       // ← ajouter
    ): Response {
        $colis = $colisRepository->find($id);

        if (!$colis) {
            $this->addFlash('error', 'Colis introuvable.');
            return $this->redirectToRoute('app_livraison_index');
        }

        if (!$colis->getLivraisons()->isEmpty()) {
            $this->addFlash('error', 'Ce colis est déjà pris en charge.');
            return $this->redirectToRoute('app_livraison_index');
        }

        $livreur   = $this->getUser();
        $livraison = new Livraison();
        $livraison->setColis($colis);
        $livraison->setLivreur($livreur);
        $livraison->setTotal($colis->calculerMontant());

        // ✅ Appel ML : distance + durée automatiques
        $prediction = $predictionService->predictComplet($colis);

        $messageETA = '';
        if ($prediction) {
            $livraison->setDistanceKm($prediction['distance_km']);
            $livraison->setDureeEstimeeMinutes($prediction['duree_minutes']);
            $messageETA = ' | 📏 ' . $prediction['distance_km'] . ' km | 🕐 ' . $prediction['duree_formatee'];
        }

        $ancienStatut = $colis->getStatut();
        $colis->setStatut('en_cours');
        $colis->setDateExpedition(new \DateTime());

        $entityManager->persist($livraison);
        $entityManager->flush();

        $notificationService->notifierChangementStatut($colis, $ancienStatut, 'en_cours');

        $this->addFlash('success', '✅ Colis pris en charge !' . $messageETA);

        return $this->redirectToRoute('app_livraison_mes_livraisons');
    }

    // ✅ Terminer : plus de formulaire, juste un POST
    #[Route('/terminer/{id}', name: 'app_livraison_terminer', methods: ['POST'])]
    public function terminer(
        Livraison $livraison,
        EntityManagerInterface $entityManager,
        NotificationService $notificationService
    ): Response {
        $livraison->setStatut('termine');
        $livraison->setDateFin(new \DateTime());

        $ancienStatut = $livraison->getColis()->getStatut();
        $livraison->getColis()->setStatut('livre');

        $entityManager->flush();

        $notificationService->notifierChangementStatut($livraison->getColis(), $ancienStatut, 'livre');

        $total = $livraison->getTotal();
        $this->addFlash('success', '✅ Livraison terminée ! Montant : ' . number_format($total, 2, ',', ' ') . ' €');

        return $this->redirectToRoute('app_livraison_mes_livraisons');
    }

    #[Route('/colis/{id}/details', name: 'app_livraison_colis_details', methods: ['GET'])]
    public function detailsColis(Colis $colis): Response
    {
        if (!$colis->estDisponible()) {
            $this->addFlash('error', 'Ce colis n\'est plus disponible.');
            return $this->redirectToRoute('app_livraison_index');
        }
        return $this->render('front/livraison/details_colis.html.twig', [
            'colis' => $colis,
        ]);
    }

    #[Route('/details/{id}', name: 'app_livraison_details', methods: ['GET'])]
    public function details(Livraison $livraison): Response
    {
        return $this->render('front/livraison/details_colis_simple.html.twig', [
            'livraison' => $livraison,
        ]);
    }
}