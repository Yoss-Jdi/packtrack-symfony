<?php

namespace App\Controller;

use App\Entity\Vehicule;
use App\Form\VehiculeType;
use App\Repository\VehiculeRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/vehicles')]
class VehicleController extends AbstractController
{
    #[Route('/', name: 'admin_vehicles_index')]
    public function index(Request $request, VehiculeRepository $repository, PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('q');
        $sort = $request->query->get('sort');
        $direction = $request->query->get('direction', 'ASC');

        $queryBuilder = $repository->getSearchAndSortQueryBuilder($search, $sort, $direction);
        $vehicles = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );
        $statsByDisponibilite = $repository->getStatsByDisponibilite();

        return $this->render('admin/vehicles/index.html.twig', [
            'vehicles' => $vehicles,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'statsByDisponibilite' => $statsByDisponibilite,
        ]);
    }

    #[Route('/export/pdf', name: 'admin_vehicles_export_pdf', methods: ['GET'])]
    public function exportPdf(VehiculeRepository $repository): Response
    {
        $vehicles = $repository->findAll();

        $html = $this->renderView('admin/vehicles/export_pdf.html.twig', [
            'vehicles' => $vehicles,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="vehicles.pdf"',
            ]
        );
    }

    #[Route('/new', name: 'admin_vehicles_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $vehicule = new Vehicule();
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($vehicule);
            $em->flush();

            $this->addFlash('success', 'Véhicule créé avec succès.');

            return $this->redirectToRoute('admin_vehicles_index');
        }

        return $this->render('admin/vehicles/form.html.twig', [
            'form' => $form->createView(),
            'vehicule' => $vehicule,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_vehicles_edit')]
    public function edit(Vehicule $vehicule, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Véhicule mis à jour avec succès.');

            return $this->redirectToRoute('admin_vehicles_index');
        }

        return $this->render('admin/vehicles/form.html.twig', [
            'form' => $form->createView(),
            'vehicule' => $vehicule,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_vehicles_delete', methods: ['POST'])]
    public function delete(Vehicule $vehicule, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_vehicule_' . $vehicule->getId(), (string) $request->request->get('_token'))) {
            $em->remove($vehicule);
            $em->flush();
            $this->addFlash('success', 'Véhicule supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_vehicles_index');
    }
}