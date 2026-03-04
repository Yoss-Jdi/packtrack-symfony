<?php

namespace App\Controller;

use App\Entity\FactureMaintenance;
use App\Repository\FactureMaintenanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/admin/factures-maintenance')]
class FactureMaintenanceController extends AbstractController
{
    #[Route('/', name: 'admin_factures_maintenance_index')]
    public function index(
        Request $request,
        FactureMaintenanceRepository $repository,
        PaginatorInterface $paginator
    ): Response {
        $queryBuilder = $repository->createQueryBuilder('f')
            ->leftJoin('f.vehicule', 'v')
            ->leftJoin('f.technician', 't')
            ->orderBy('f.dateEmission', 'DESC');

        $factures = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        $totalCost = $repository->getTotalMaintenanceCost();

        return $this->render('admin/factures_maintenance/index.html.twig', [
            'factures' => $factures,
            'totalCost' => $totalCost,
        ]);
    }

    #[Route('/{id}', name: 'admin_factures_maintenance_show', requirements: ['id' => '\d+'])]
    public function show(FactureMaintenance $facture): Response
    {
        return $this->render('admin/factures_maintenance/show.html.twig', [
            'facture' => $facture,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_factures_maintenance_delete', methods: ['POST'])]
    public function delete(
        FactureMaintenance $facture,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete_facture_' . $facture->getId(), (string) $request->request->get('_token'))) {
            $em->remove($facture);
            $em->flush();
            $this->addFlash('success', 'Facture supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_factures_maintenance_index');
    }

    #[Route('/{id}/pdf', name: 'admin_factures_maintenance_pdf')]
    public function generatePdf(FactureMaintenance $facture): Response
    {
        $html = $this->renderView('admin/factures_maintenance/pdf.html.twig', [
            'facture' => $facture,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="facture_' . $facture->getNumero() . '.pdf"',
            ]
        );
    }
}
