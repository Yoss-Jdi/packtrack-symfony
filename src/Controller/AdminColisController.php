<?php

namespace App\Controller;

use App\Entity\Colis;
use App\Repository\ColisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/parcels')]
class AdminColisController extends AbstractController
{
    #[Route('', name: 'admin_parcels', methods: ['GET'])]
    //#[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, ColisRepository $colisRepository): Response
    {
        $statusFilter = $request->query->get('status', 'all');

        $colis = $statusFilter === 'all'
            ? $colisRepository->findAll()
            : $colisRepository->findBy(['statut' => $statusFilter]);

        // Stats toujours calculé
        $allColis = $colisRepository->findAll();
        $stats = [
            'total'      => count($allColis),
            'en_attente' => count(array_filter($allColis, fn($c) => $c->getStatut() === 'en_attente')),
            'en_cours'   => count(array_filter($allColis, fn($c) => $c->getStatut() === 'en_cours')),
            'livre'      => count(array_filter($allColis, fn($c) => $c->getStatut() === 'livre')),
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/parcels/_table.html.twig', [
                'colis_list' => $colis,
            ]);
        }

        return $this->render('admin/parcels/index.html.twig', [
            'colis_list'   => $colis,
            'stats'        => $stats,
            'activeFilter' => $statusFilter,
        ]);
    }

    #[Route('/{id}', name: 'admin_colis_show', methods: ['GET'])]
    //#[IsGranted('ROLE_ADMIN')]
    public function show(Colis $colis): Response
    {
        return $this->render('admin/parcels/show.html.twig', [
            'colis' => $colis,
        ]);
    }
}