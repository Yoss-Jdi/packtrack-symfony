<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\FactureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FactureFrontController extends AbstractController
{
    #[Route('/mes-factures', name: 'app_facture_front', methods: ['GET'])]
    public function index(FactureRepository $factureRepository): Response
    {
        $user = $this->getUser();

        if (!$user instanceof \App\Entity\Utilisateurs) {
            return $this->redirectToRoute('app_login');
        }

        $userId         = $user->getId();
        $toutesFactures = $factureRepository->findAll();
        $role           = $user->getRole();
        $factures       = [];

        if ($role === Role::LIVREUR) {
            // Livreur → ses propres livraisons
            foreach ($toutesFactures as $f) {
                if ($f->getLivraison()?->getLivreur()?->getId() === $userId) {
                    $factures[] = $f;
                }
            }

        } elseif ($role === Role::CLIENT) {
            // CLIENT → expéditeur OU destinataire du colis (via les relations sur Colis)
            foreach ($toutesFactures as $f) {
                $colis = $f->getLivraison()?->getColis();
                if (!$colis) continue;

                $estExpediteur   = $colis->getExpediteur()?->getId() === $userId;
                $estDestinataire = $colis->getDestinataire()?->getId() === $userId;

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