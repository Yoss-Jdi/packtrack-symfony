<?php

namespace App\Controller;

use App\Entity\Vehicule;
use App\Form\VehiculeType;
use App\Repository\VehiculeRepository;
use App\Service\VehicleProblemAnalyzer;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
            3
        );
        $statsByType = $repository->getStatsByType();

        return $this->render('admin/vehicles/index.html.twig', [
            'vehicles' => $vehicles,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'statsByType' => $statsByType,
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
    public function new(
        Request $request, 
        EntityManagerInterface $em,
        VehicleProblemAnalyzer $problemAnalyzer,
        MailerInterface $mailer,
        ParameterBagInterface $params
    ): Response
    {
        $vehicule = new Vehicule();
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update technician status if assigned
            if ($vehicule->getTechnician() && $vehicule->getStatut() !== 'disponible') {
                $vehicule->getTechnician()->setStatut('occupe');
            }

            $em->persist($vehicule);
            $em->flush();

            // Si le statut est "hors_service" ou "en_maintenance" et qu'il y a une description
            if (in_array($vehicule->getStatut(), ['hors_service', 'en_maintenance']) 
                && $vehicule->getProblemDescription() 
                && $vehicule->getTechnician()) {
                
                $this->sendTechnicianNotification(
                    $vehicule, 
                    $problemAnalyzer, 
                    $mailer, 
                    $params
                );
            }

            $this->addFlash('success', 'Véhicule créé avec succès.');

            return $this->redirectToRoute('admin_vehicles_index');
        }

        return $this->render('admin/vehicles/form.html.twig', [
            'form' => $form->createView(),
            'vehicule' => $vehicule,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_vehicles_edit')]
    public function edit(
        Vehicule $vehicule, 
        Request $request, 
        EntityManagerInterface $em,
        VehicleProblemAnalyzer $problemAnalyzer,
        MailerInterface $mailer,
        ParameterBagInterface $params
    ): Response
    {
        $originalStatut = $vehicule->getStatut();
        $originalDescription = $vehicule->getProblemDescription();
        $originalTechnician = $vehicule->getTechnician();
        
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newTechnician = $vehicule->getTechnician();
            $newStatut = $vehicule->getStatut();

            // Handle technician status changes
            // If technician was removed or changed, set old technician to disponible
            if ($originalTechnician && $originalTechnician !== $newTechnician) {
                $originalTechnician->setStatut('disponible');
            }

            // If vehicle status changed to disponible, set technician to disponible and remove assignment
            if ($newStatut === 'disponible') {
                if ($newTechnician) {
                    $newTechnician->setStatut('disponible');
                }
                $vehicule->setTechnician(null);
            } 
            // If new technician assigned and vehicle not disponible, set technician to occupe
            elseif ($newTechnician && $newTechnician !== $originalTechnician) {
                $newTechnician->setStatut('occupe');
            }

            $em->flush();

            // Envoyer notification si le statut change vers "hors_service" ou "en_maintenance"
            // OU si la description change pour un véhicule déjà en maintenance/hors service
            $statusChanged = in_array($vehicule->getStatut(), ['hors_service', 'en_maintenance']) 
                && $originalStatut !== $vehicule->getStatut();
            
            $descriptionChanged = in_array($vehicule->getStatut(), ['hors_service', 'en_maintenance'])
                && $vehicule->getProblemDescription() !== $originalDescription
                && !empty($vehicule->getProblemDescription());

            $technicianChanged = $newTechnician && $newTechnician !== $originalTechnician;

            if (($statusChanged || $descriptionChanged || $technicianChanged) 
                && $vehicule->getProblemDescription() 
                && $vehicule->getTechnician()) {
                
                $this->sendTechnicianNotification(
                    $vehicule, 
                    $problemAnalyzer, 
                    $mailer, 
                    $params
                );
            }

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

    private function sendTechnicianNotification(
        Vehicule $vehicule,
        VehicleProblemAnalyzer $problemAnalyzer,
        MailerInterface $mailer,
        ParameterBagInterface $params
    ): void
    {
        try {
            // Analyser le problème avec l'IA
            $analysis = $problemAnalyzer->analyzeProblem(
                $vehicule->getProblemDescription(),
                $vehicule->getMarque(),
                $vehicule->getModele(),
                $vehicule->getTypeVehicule()
            );

            // Envoyer l'email au technicien
            $email = (new TemplatedEmail())
                ->from($params->get('app.mailer_from_address'))
                ->to($vehicule->getTechnician()->getEmail())
                ->subject('Nouvelle intervention requise - ' . $vehicule->getMarque() . ' ' . $vehicule->getModele())
                ->htmlTemplate('emails/technician_notification.html.twig')
                ->context([
                    'vehicule' => $vehicule,
                    'technician' => $vehicule->getTechnician(),
                    'analysis' => $analysis,
                ]);

            $mailer->send($email);

            $this->addFlash('info', 'Une notification a été envoyée au technicien ' . $vehicule->getTechnician()->getPrenom() . ' ' . $vehicule->getTechnician()->getNom());
        } catch (\Exception $e) {
            $this->addFlash('warning', 'Le véhicule a été enregistré mais l\'envoi de l\'email a échoué: ' . $e->getMessage());
        }
    }
}
