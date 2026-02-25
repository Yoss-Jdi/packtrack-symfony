<?php

namespace App\Controller;

use App\Entity\Role;
use App\Repository\FactureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FactureFrontController extends AbstractController
{
    #[Route('/mes-factures', name: 'app_facture_front', methods: ['GET'])]
    public function index(FactureRepository $factureRepository): Response
    {
        $user     = $this->getUser();
        $factures = [];

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $toutesFactures = $factureRepository->findAll();
        $role           = $user->getRole();

        if ($role === Role::LIVREUR) {
            // Livreur → ses propres livraisons
            foreach ($toutesFactures as $f) {
                if ($f->getLivraison()?->getLivreur()?->getId() === $user->getId()) {
                    $factures[] = $f;
                }
            }

        } elseif ($role === Role::EXPEDITEUR) {
            // Expéditeur → ses colis envoyés
            foreach ($toutesFactures as $f) {
                if ($f->getLivraison()?->getColis()?->getExpediteur()?->getId() === $user->getId()) {
                    $factures[] = $f;
                }
            }

        } elseif ($role === Role::DESTINATAIRE) {
            // Destinataire → ses colis reçus
            foreach ($toutesFactures as $f) {
                if ($f->getLivraison()?->getColis()?->getDestinataire()?->getId() === $user->getId()) {
                    $factures[] = $f;
                }
            }

        } elseif ($role === Role::CLIENT) {
            // CLIENT → expéditeur OU destinataire
            foreach ($toutesFactures as $f) {
                $colis = $f->getLivraison()?->getColis();
                if (!$colis) continue;

                $estExpediteur   = $colis->getExpediteur()?->getId() === $user->getId();
                $estDestinataire = $colis->getDestinataire()?->getId() === $user->getId();

                if ($estExpediteur || $estDestinataire) {
                    $factures[] = $f;
                }
            }

        } elseif ($role === Role::ENTREPRISE || $role === Role::ADMIN) {
            // Entreprise/Admin → toutes les factures
            $factures = $toutesFactures;
        }

        $total = array_sum(array_map(fn($f) => $f->getMontantTTC(), $factures));

        return $this->render('front/facture_front.html.twig', [
            'factures' => $factures,
            'total'    => $total,
            'role'     => $role,
        ]);
    }
}