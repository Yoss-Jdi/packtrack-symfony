<?php

namespace App\Controller;

use App\Entity\Livraison;
use App\Entity\Colis;
use App\Entity\Facture;
use App\Entity\Recompense;
use App\Entity\Role;
use App\Form\FactureType;
use App\Service\CloudinaryService;
use App\Service\FraudeDetectionService;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/facture')]
final class FactureController extends AbstractController
{
    // ─────────────────────────────────────────
    //  VERIFICATION SESSION ADMIN
    // ─────────────────────────────────────────
    private function checkAdminAccess(): ?Response
    {
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');
        if ($user->getRole() !== Role::ADMIN) return $this->redirectToRoute('app_login');
        return null;
    }

    // ─────────────────────────────────────────
    //  INDEX
    // ─────────────────────────────────────────
    #[Route(name: 'app_facture_index', methods: ['GET'])]
    public function index(): Response
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        return $this->render('admin/factures/index_new.html.twig');
    }

    // ─────────────────────────────────────────
    //  SEARCH
    // ─────────────────────────────────────────
    #[Route('/search', name: 'app_facture_search', methods: ['GET'])]
    public function search(Request $request, FactureRepository $factureRepository): Response
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        $statut         = $request->query->get('statut');
        $periode        = $request->query->get('periode');
        $dateSpecifique = $request->query->get('dateSpecifique');

        $factures = $factureRepository->searchByStatutEtPeriode($statut, $periode, $dateSpecifique);

        return $this->render('admin/factures/_tableau.html.twig', [
            'factures' => $factures,
        ]);
    }

    // ─────────────────────────────────────────
    //  NEW
    // ─────────────────────────────────────────
    #[Route('/new', name: 'app_facture_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        FactureRepository $factureRepository,
        ValidatorInterface $validator
    ): Response {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        $facture = new Facture();
        $form    = $this->createForm(FactureType::class, $facture);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $errors = [];
            $colis  = $form->get('colis')->getData();

            if (!$colis) {
                $errors['colis'] = 'Vous devez obligatoirement sélectionner un colis.';
            }

            $livraison = null;
            if ($colis) {
                $livraison = $entityManager->getRepository(Livraison::class)
                    ->findOneBy(['colis' => $colis]);

                if (!$livraison) {
                    $errors['colis'] = 'Aucune livraison trouvée pour ce colis.';
                } elseif ($livraison->getStatut() !== 'termine') {
                    $errors['colis'] = 'La livraison doit être terminée. Statut actuel : ' . $livraison->getStatut();
                } elseif ($factureRepository->findOneBy(['livraison' => $livraison])) {
                    $errors['colis'] = 'Cette livraison a déjà une facture associée.';
                } elseif ($livraison->getTotal() === null || $livraison->getTotal() <= 0) {
                    $errors['colis'] = 'La livraison ne contient pas de montant valide.';
                }
            }

            if (empty($errors)) {

                $lastFacture = $factureRepository->findOneBy([], ['ID_Facture' => 'DESC']);
                $nextNumber  = 1;
                if ($lastFacture) {
                    $nextNumber = (int) substr($lastFacture->getNumero(), 4) + 1;
                }
                $numeroFacture = 'FAC-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

                if ($factureRepository->findOneBy(['numero' => $numeroFacture])) {
                    $this->addFlash('error', 'Erreur lors de la génération du numéro. Veuillez réessayer.');
                    return $this->render('admin/factures/new.html.twig', [
                        'form'    => $form->createView(),
                        'facture' => $facture,
                        'errors'  => [],
                    ]);
                }

                $montantHT  = $livraison->getTotal();
                $tva        = $montantHT * 0.20;
                $montantTTC = $montantHT + $tva;

                $facture->setNumero($numeroFacture);
                $facture->setMontantHT($montantHT);
                $facture->setTva($tva);
                $facture->setMontantTTC($montantTTC);
                $facture->setDateEmission(new \DateTime());
                $facture->setStatut('emise');
                $facture->setLivraison($livraison);

                try {
                    $entityManager->persist($facture);
                    $entityManager->flush();
                    $this->creerRecompenseAutomatique($facture, $entityManager);
                    $this->addFlash('success', '✅ Facture N° ' . $facture->getNumero() . ' créée avec succès !');
                    return $this->redirectToRoute('app_facture_index');
                } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                    $this->addFlash('error', '❌ Ce numéro de facture existe déjà. Veuillez réessayer.');
                } catch (\Exception $e) {
                    $this->addFlash('error', '❌ Une erreur est survenue : ' . $e->getMessage());
                }
            }

            return $this->render('admin/factures/new.html.twig', [
                'form'    => $form->createView(),
                'facture' => $facture,
                'errors'  => $errors,
            ]);
        }

        return $this->render('admin/factures/new.html.twig', [
            'form'    => $form->createView(),
            'facture' => $facture,
            'errors'  => [],
        ]);
    }

    // ─────────────────────────────────────────
    //  STATS
    // ─────────────────────────────────────────
    #[Route('/stats', name: 'app_facture_stats', methods: ['GET'])]
    public function stats(FactureRepository $factureRepository): Response
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        $factures      = $factureRepository->findAll();
        $totalFactures = count($factures);
        $totalCA = $totalHT = $totalTVA = 0;
        $facturesPayees = $facturesImpayees = 0;

        $now          = new \DateTime();
        $debutSemaine = (clone $now)->modify('monday this week');
        $debutMois    = new \DateTime('first day of this month');
        $debutAnnee   = new \DateTime('first day of january this year');

        $caSemaine = $caMois = $caAnnee = 0;

        $graphiqueMensuel = [];
        for ($i = 11; $i >= 0; $i--) {
            $mois = (clone $now)->modify("-$i months");
            $graphiqueMensuel[$mois->format('M Y')] = 0;
        }

        $topClients = [];

        foreach ($factures as $facture) {
            $totalCA  += $facture->getMontantTTC();
            $totalHT  += $facture->getMontantHT();
            $totalTVA += $facture->getTva();

            $facture->getStatut() === 'payee' ? $facturesPayees++ : $facturesImpayees++;

            $dateFacture = $facture->getDateEmission();
            if ($dateFacture >= $debutSemaine) $caSemaine += $facture->getMontantTTC();
            if ($dateFacture >= $debutMois)    $caMois    += $facture->getMontantTTC();
            if ($dateFacture >= $debutAnnee)   $caAnnee   += $facture->getMontantTTC();

            $moisCle = $dateFacture->format('M Y');
            if (isset($graphiqueMensuel[$moisCle])) {
                $graphiqueMensuel[$moisCle] += $facture->getMontantTTC();
            }

            $livraison = $facture->getLivraison();
            if ($livraison?->getColis()?->getDestinataire()) {
                $dest      = $livraison->getColis()->getDestinataire();
                $clientNom = $dest->getPrenom() . ' ' . $dest->getNom();
                $topClients[$clientNom] = ($topClients[$clientNom] ?? 0) + $facture->getMontantTTC();
            }
        }

        arsort($topClients);
        $topClients = array_slice($topClients, 0, 5, true);
        $moyenne    = $totalFactures > 0 ? $totalCA / $totalFactures : 0;

        return $this->render('admin/factures/stats.html.twig', [
            'totalFactures'    => $totalFactures,
            'totalCA'          => $totalCA,
            'totalHT'          => $totalHT,
            'totalTVA'         => $totalTVA,
            'moyenne'          => $moyenne,
            'facturesPayees'   => $facturesPayees,
            'facturesImpayees' => $facturesImpayees,
            'caSemaine'        => $caSemaine,
            'caMois'           => $caMois,
            'caAnnee'          => $caAnnee,
            'graphiqueMensuel' => $graphiqueMensuel,
            'topClients'       => $topClients,
        ]);
    }

    // ─────────────────────────────────────────
    //  API COLIS MONTANT
    // ─────────────────────────────────────────
    #[Route('/api/colis/{id}/montant', name: 'api_colis_montant', methods: ['GET'])]
    public function getColisMontant(int $id, EntityManagerInterface $entityManager): Response
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        $livraison = $entityManager->getRepository(Livraison::class)
            ->findOneBy(['colis' => $id]);

        if (!$livraison || !$livraison->getTotal()) {
            return $this->json(['montant' => null, 'error' => 'Aucune livraison trouvée']);
        }

        return $this->json([
            'montant' => $livraison->getTotal(),
            'statut'  => $livraison->getStatut(),
        ]);
    }

    // ─────────────────────────────────────────
    //  CALENDAR
    // ─────────────────────────────────────────
    #[Route('/calendar', name: 'app_facture_calendar', methods: ['GET'])]
    public function calendar(): Response
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;
        return $this->render('admin/factures/calendar.html.twig');
    }

    #[Route('/calendar/events', name: 'app_facture_calendar_events', methods: ['GET'])]
    public function calendarEvents(FactureRepository $factureRepository): Response
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        $factures = $factureRepository->findAll();
        $events   = [];

        foreach ($factures as $facture) {
            $events[] = [
                'id'            => $facture->getId(),
                'title'         => $facture->getNumero() . ' — ' . number_format($facture->getMontantTTC(), 2) . ' DT',
                'start'         => $facture->getDateEmission()->format('Y-m-d'),
                'color'         => match ($facture->getStatut()) {
                    'payee'   => '#198754',
                    'emise'   => '#ffc107',
                    'annulee' => '#dc3545',
                    default   => '#0d6efd',
                },
                'url'           => '/facture/' . $facture->getId(),
                'extendedProps' => ['statut' => $facture->getStatut()],
            ];
        }

        return $this->json($events);
    }

    // ─────────────────────────────────────────
    //  PREVISION CA — IA
    // ─────────────────────────────────────────
    #[Route('/prevision-ca', name: 'app_facture_prevision_ca', methods: ['GET'])]
    public function previsionCA(FactureRepository $factureRepository): Response
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        $factures  = $factureRepository->findAll();
        $caParMois = [];
        $nbParMois = [];

        foreach ($factures as $facture) {
            $mois = (int) $facture->getDateEmission()->format('n');
            $caParMois[$mois] = ($caParMois[$mois] ?? 0) + $facture->getMontantTTC();
            $nbParMois[$mois] = ($nbParMois[$mois] ?? 0) + 1;
        }

        $moisActuel = (int) date('n');
        $moisFuturs = [];

        for ($i = 1; $i <= 3; $i++) {
            $moisFutur    = (($moisActuel + $i - 1) % 12) + 1;
            $moisFuturs[] = [
                'mois'              => $moisFutur,
                'nb_factures_prevu' => (int) round(array_sum($nbParMois) / max(count($nbParMois), 1)),
            ];
        }

        $inputData  = json_encode(['mois_futurs' => $moisFuturs]);
        $projectDir = $this->getParameter('kernel.project_dir');

        $scriptPath = $projectDir . '/ml/predict_ca.py';
        $inputFile  = $projectDir . '/ml/input.json';

        file_put_contents($inputFile, $inputData);

        $command = "python " . escapeshellarg($scriptPath);
        $output  = shell_exec($command);

        $predictionsData = json_decode($output, true);

        if (!$predictionsData || !isset($predictionsData['predictions'])) {
            $this->addFlash('error', '❌ Erreur lors de l\'exécution du modèle IA.');
            return $this->redirectToRoute('app_facture_index');
        }

        $predictions = [];
        $base = array_slice(array_values($caParMois), -3);

        foreach ($predictionsData['predictions'] as $pred) {
            $tendance = (!empty($base) && $pred['ca_prevu'] >= end($base))
                ? 'hausse'
                : 'baisse';

            $predictions[] = [
                'mois'      => $pred['mois'],
                'ca_prevu'  => $pred['ca_prevu'],
                'tendance'  => $tendance,
                'base_sur'  => 'Basé sur: ' . implode(', ', $base) . ' DT'
            ];

            array_unshift($base, $pred['ca_prevu']);
            $base = array_slice($base, 0, 3);
        }

        $historiqueCA = [];
        $moisNoms     = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];

        for ($i = 5; $i >= 0; $i--) {
            $mois = (($moisActuel - $i - 1 + 12) % 12) + 1;
            $historiqueCA[] = [
                'mois' => $moisNoms[$mois - 1],
                'ca'   => $caParMois[$mois] ?? 0,
                'reel' => true,
            ];
        }

        foreach ($predictions as $pred) {
            $historiqueCA[] = [
                'mois' => $pred['mois'],
                'ca'   => $pred['ca_prevu'],
                'reel' => false,
            ];
        }

        return $this->render('admin/factures/prevision_ca.html.twig', [
            'predictions'  => $predictions,
            'historiqueCA' => $historiqueCA,
            'caParMois'    => $caParMois,
        ]);
    }

    #[Route('/prevision-ca/entrainer', name: 'app_facture_entrainer_ia', methods: ['GET'])]
    public function entrainerIA(FactureRepository $factureRepository): Response
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        $factures  = $factureRepository->findAll();
        $caParMois = [];
        $nbParMois = [];

        foreach ($factures as $facture) {
            $mois = (int) $facture->getDateEmission()->format('n');
            $caParMois[$mois] = ($caParMois[$mois] ?? 0) + $facture->getMontantTTC();
            $nbParMois[$mois] = ($nbParMois[$mois] ?? 0) + 1;
        }

        $trainData = [];
        for ($mois = 1; $mois <= 12; $mois++) {
            $trainData[] = [
                'mois'         => $mois,
                'nb_factures'  => $nbParMois[$mois] ?? 0,
                'ca'           => $caParMois[$mois] ?? 0,
            ];
        }

        $trainFile = $this->getParameter('kernel.project_dir') . '/ml/train_data.json';
        file_put_contents($trainFile, json_encode(['data' => $trainData]));

        $scriptPath = $this->getParameter('kernel.project_dir') . '/ml/train_ca.py';
        $command    = "python " . escapeshellarg($scriptPath);
        $output     = shell_exec($command);

        $this->addFlash('success', '✅ Modèle IA re-entraîné avec vos données réelles ! ' . $output);
        return $this->redirectToRoute('app_facture_prevision_ca');
    }

    // ─────────────────────────────────────────
    //  🔏 VERIFIER FRAUDE IA  ← NOUVEAU
    // ─────────────────────────────────────────
    #[Route('/{id}/verifier-fraude', name: 'app_facture_verifier_fraude', methods: ['POST'])]
    public function verifierFraude(
        int $id,
        FactureRepository $factureRepository,
        FraudeDetectionService $fraudeService
    ): JsonResponse {
        if ($redirect = $this->checkAdminAccess()) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }

        // Récupérer la facture
        $facture = $factureRepository->find($id);
        if (!$facture) {
            return $this->json(['error' => 'Facture introuvable'], 404);
        }

        // Vérifier que le PDF existe
        if (!$facture->getPdfUrl()) {
            return $this->json(['error' => 'Aucun PDF associé à cette facture'], 400);
        }

        // Appeler le service IA
            $resultat = $fraudeService->verifierFacture($facture);
        return $this->json($resultat);
    }

    // ─────────────────────────────────────────
    //  SHOW → redirige vers EDIT
    // ─────────────────────────────────────────
    #[Route('/{id}', name: 'app_facture_show', methods: ['GET'])]
    public function show(?Facture $facture): Response
    {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        if (!$facture) {
            $this->addFlash('error', 'La facture demandée n\'existe pas.');
            return $this->redirectToRoute('app_facture_index');
        }

        return $this->redirectToRoute('app_facture_edit', ['id' => $facture->getId()]);
    }

    // ─────────────────────────────────────────
    //  EDIT
    // ─────────────────────────────────────────
    #[Route('/{id}/edit', name: 'app_facture_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Facture $facture,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        $errors = [];

        if ($request->isMethod('POST')) {
            $numero       = $request->request->get('numero');
            $dateEmission = $request->request->get('dateEmission');
            $montantHT    = $request->request->get('montantHT');

            $facture->setNumero($numero ?? '');

            if (is_numeric($montantHT)) {
                $facture->setMontantHT((float) $montantHT);
                $facture->setTva((float) $montantHT * 0.20);
                $facture->setMontantTTC((float) $montantHT * 1.20);
            }

            if ($dateEmission) {
                try {
                    $facture->setDateEmission(new \DateTime($dateEmission));
                } catch (\Exception) {
                    $errors['dateEmission'] = 'La date est invalide.';
                }
            } else {
                $facture->setDateEmission(null);
            }

            $violations = $validator->validate($facture);
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            if (empty($errors)) {
                $entityManager->flush();
                $this->addFlash('success', '✅ Facture modifiée avec succès !');
                return $this->redirectToRoute('app_facture_index');
            }
        }

        return $this->render('admin/factures/edit.html.twig', [
            'facture' => $facture,
            'errors'  => $errors,
        ]);
    }

    // ─────────────────────────────────────────
    //  PDF
    // ─────────────────────────────────────────
    #[Route('/{id}/pdf', name: 'app_facture_pdf', methods: ['GET'])]
    public function pdf(
        Facture $facture,
        EntityManagerInterface $entityManager,
        CloudinaryService $cloudinaryService
    ): Response {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $html   = $this->renderView('admin/factures/pdf.html.twig', ['facture' => $facture]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();

        if (!$facture->getPdfUrl()) {
            $tempFile = sys_get_temp_dir() . '/facture_' . $facture->getNumero() . '.pdf';
            file_put_contents($tempFile, $pdfContent);

            try {
                $url = $cloudinaryService->uploadPdf($tempFile, 'facture_' . $facture->getNumero());
                $facture->setPdfUrl($url);
                $entityManager->flush();
            } catch (\Exception $e) {
                // Upload Cloudinary échoué — on continue
            } finally {
                if (file_exists($tempFile)) unlink($tempFile);
            }
        }

        return new Response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="facture-' . $facture->getNumero() . '.pdf"',
        ]);
    }

    // ─────────────────────────────────────────
    //  DELETE
    // ─────────────────────────────────────────
    #[Route('/{id}', name: 'app_facture_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Facture $facture,
        EntityManagerInterface $entityManager
    ): Response {
        if ($redirect = $this->checkAdminAccess()) return $redirect;

        if ($this->isCsrfTokenValid('delete' . $facture->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($facture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_facture_index', [], Response::HTTP_SEE_OTHER);
    }

    // ─────────────────────────────────────────
    //  PRIVATE — RECOMPENSE AUTO
    // ─────────────────────────────────────────
    private function creerRecompenseAutomatique(Facture $facture, EntityManagerInterface $entityManager): void
    {
        $livreur = $facture->getLivraison()->getLivreur();
        if (!$livreur) return;

        $recompense = new Recompense();
        $recompense->setType('Commission');
        $recompense->setValeur($facture->getMontantTTC() * 0.05);
        $recompense->setDescription('Commission 5% sur facture ' . $facture->getNumero());
        $recompense->setLivreur($livreur);
        $recompense->setFacture($facture);
        $recompense->setDateObtention(new \DateTime());

        $entityManager->persist($recompense);
        $entityManager->flush();
    }
}