<?php

namespace App\Controller;

use App\Entity\Recompense;
use App\Entity\Utilisateurs;
use App\Entity\Facture;
use App\Entity\Role;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

use App\Repository\RecompenseRepository;
use App\Repository\UtilisateursRepository;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/recompense')]
final class RecompenseController extends AbstractController
{
    #[Route(name: 'app_recompense_index', methods: ['GET'])]
    public function index(
        Request $request,
        RecompenseRepository $recompenseRepository,
        UtilisateursRepository $utilisateurRepository,
        FactureRepository $factureRepository
    ): Response {
        $searchType    = $request->query->get('type');
        $searchLivreur = $request->query->get('livreur');

        $queryBuilder = $recompenseRepository->createQueryBuilder('r')
            ->leftJoin('r.livreur', 'l')
            ->leftJoin('r.facture', 'f')
            ->addSelect('l', 'f')
            ->orderBy('r.dateObtention', 'DESC');

        if ($searchType && $searchType !== '') {
            $queryBuilder->andWhere('r.type = :type')
                ->setParameter('type', $searchType);
        }

        if ($searchLivreur && $searchLivreur !== '') {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('l.Nom', ':livreur'),
                    $queryBuilder->expr()->like('l.Prenom', ':livreur')
                )
            )->setParameter('livreur', '%' . $searchLivreur . '%');
        }

        $recompenses = $queryBuilder->getQuery()->getResult();
        $livreurs    = $utilisateurRepository->findBy(['role' => Role::LIVREUR]);
        $factures    = $factureRepository->findAll();

        return $this->render('admin/recompenses/index.html.twig', [
            'recompenses'   => $recompenses,
            'searchType'    => $searchType,
            'searchLivreur' => $searchLivreur,
            'livreurs'      => $livreurs,
            'factures'      => $factures,
            'hfApiKey'      => $_ENV['HUGGINGFACE_API_KEY'] ?? '',
        ]);
    }
#[Route('/generate-description', name: 'app_recompense_generate_description', methods: ['POST'])]
public function generateDescription(
    Request $request,
    HttpClientInterface $httpClient
): Response {
    $type   = $request->request->get('type');
    $valeur = $request->request->get('valeur');
    $prenom = $request->request->get('prenom');
    $nom    = $request->request->get('nom');

    $prompt = "Génère une courte description motivante en français (2-3 phrases) pour une récompense de type $type d'une valeur de $valeur DT attribuée au livreur $prenom $nom. Réponds uniquement avec la description.";

    try {
        // ← NOUVELLE URL avec nouveau endpoint
        $response = $httpClient->request('POST',
            'https://router.huggingface.co/v1/chat/completions',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . ($_ENV['HUGGINGFACE_API_KEY'] ?? ''),
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'    => 'meta-llama/Llama-3.1-8B-Instruct',
                    'messages' => [
                        [
                            'role'    => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 150,
                ],
            ]
        );

        $data        = $response->toArray();
        $description = $data['choices'][0]['message']['content'] ?? 'Description non disponible';

        return $this->json(['success' => true, 'description' => trim($description)]);

    } catch (\Exception $e) {
        return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}
    #[Route('/new', name: 'app_recompense_new', methods: ['POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UtilisateursRepository $utilisateurRepository,
        FactureRepository $factureRepository,
        MailerInterface $mailer
    ): Response {
        $recompense  = new Recompense();
        $type        = $request->request->get('type');
        $valeur      = $request->request->get('valeur');
        $description = $request->request->get('description');
        $seuil       = $request->request->get('seuil');
        $livreurId   = $request->request->get('livreur');
        $factureId   = $request->request->get('facture');

        if ($type === 'Commission' || $type === 'Bonus Mensuel') {
            $this->addFlash('error', '⛔ Les Commissions et Bonus Mensuels sont générés automatiquement.');
            return $this->redirectToRoute('app_recompense_index');
        }

        if (!$livreurId) {
            $this->addFlash('error', 'Vous devez sélectionner un livreur.');
            return $this->redirectToRoute('app_recompense_index');
        }

        $livreur = $utilisateurRepository->find($livreurId);

        if (!$livreur) {
            $this->addFlash('error', 'Livreur introuvable.');
            return $this->redirectToRoute('app_recompense_index');
        }

        if ($livreur->getRole() !== Role::LIVREUR) {
            $this->addFlash('error', 'L\'utilisateur sélectionné n\'est pas un livreur.');
            return $this->redirectToRoute('app_recompense_index');
        }

        $facture = null;
        if ($factureId) {
            $facture = $factureRepository->find($factureId);
        }

        $recompense->setType($type);
        $recompense->setValeur((float)$valeur);
        $recompense->setDescription($description);
        $recompense->setSeuil($seuil ? (int)$seuil : null);
        $recompense->setLivreur($livreur);
        $recompense->setFacture($facture);
        $recompense->setDateObtention(new \DateTime());

        try {
            $entityManager->persist($recompense);
            $entityManager->flush();

            if ($livreur->getEmail()) {
                $email = (new Email())
                    ->from($_ENV['MAILER_FROM'] ?? 'noreply@livraison.tn')
                    ->to($livreur->getEmail())
                    ->subject('🏆 Félicitations ! Vous avez reçu une récompense')
                    ->html('
                        <div style="font-family: Segoe UI, sans-serif; max-width: 600px; margin: auto; padding: 30px; background: #f4f6fb; border-radius: 16px;">
                            <div style="background: linear-gradient(135deg, #667eea, #764ba2); padding: 25px; border-radius: 12px; text-align: center;">
                                <h1 style="color: white; margin: 0;">🏆 Félicitations !</h1>
                            </div>
                            <div style="background: white; padding: 25px; border-radius: 12px; margin-top: 20px;">
                                <p style="font-size: 16px; color: #2d3748;">Bonjour <strong>' . $livreur->getPrenom() . ' ' . $livreur->getNom() . '</strong>,</p>
                                <p style="color: #4a5568;">Vous avez reçu une nouvelle récompense !</p>
                                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                                    <tr style="background: #f8f6ff;">
                                        <td style="padding: 10px; font-weight: bold; color: #6f42c1;">Type</td>
                                        <td style="padding: 10px;">' . $recompense->getType() . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px; font-weight: bold; color: #6f42c1;">Valeur</td>
                                        <td style="padding: 10px;">' . number_format($recompense->getValeur(), 2) . ' DT</td>
                                    </tr>
                                    <tr style="background: #f8f6ff;">
                                        <td style="padding: 10px; font-weight: bold; color: #6f42c1;">Description</td>
                                        <td style="padding: 10px;">' . ($recompense->getDescription() ?? '-') . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px; font-weight: bold; color: #6f42c1;">Date</td>
                                        <td style="padding: 10px;">' . $recompense->getDateObtention()->format('d/m/Y') . '</td>
                                    </tr>
                                </table>
                                <p style="margin-top: 20px; color: #4a5568;">Continuez votre excellent travail ! 🚀</p>
                            </div>
                        </div>
                    ');
                $mailer->send($email);
            }

            $this->addFlash('success', '✅ Récompense créée et email envoyé au livreur !');

        } catch (\Exception $e) {
            $this->addFlash('error', '❌ Erreur lors de la création : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_recompense_index');
    }

    #[Route('/stats', name: 'app_recompense_stats', methods: ['GET'])]
    public function stats(RecompenseRepository $recompenseRepository): Response
    {
        $recompenses = $recompenseRepository->findAll();

        $parLivreur  = [];
        $parMois     = [];
        $topLivreurs = [];

        foreach ($recompenses as $r) {
            if (!$r->getLivreur()) continue;

            $nomLivreur = $r->getLivreur()->getPrenom() . ' ' . $r->getLivreur()->getNom();
            $mois       = $r->getDateObtention()->format('M Y');

            if (!isset($parLivreur[$nomLivreur])) $parLivreur[$nomLivreur] = 0;
            $parLivreur[$nomLivreur] += $r->getValeur();

            if (!isset($parMois[$mois])) $parMois[$mois] = 0;
            $parMois[$mois] += $r->getValeur();

            if (!isset($topLivreurs[$nomLivreur])) $topLivreurs[$nomLivreur] = 0;
            $topLivreurs[$nomLivreur] += $r->getValeur();
        }

        arsort($topLivreurs);
        $topLivreurs = array_slice($topLivreurs, 0, 5, true);

        return $this->render('admin/recompenses/stats.html.twig', [
            'parLivreur'  => $parLivreur,
            'parMois'     => $parMois,
            'topLivreurs' => $topLivreurs,
        ]);
    }

    #[Route('/automatiques', name: 'app_recompense_automatiques', methods: ['GET'])]
    public function automatiques(RecompenseRepository $recompenseRepository): Response
    {
        $recompenses = $recompenseRepository->createQueryBuilder('r')
            ->leftJoin('r.livreur', 'l')
            ->leftJoin('r.facture', 'f')
            ->addSelect('l', 'f')
            ->where('r.type = :type')
            ->setParameter('type', 'Commission')
            ->orderBy('r.dateObtention', 'DESC')
            ->getQuery()->getResult();

        return $this->render('admin/recompenses/automatiques.html.twig', [
            'recompenses' => $recompenses,
        ]);
    }

    #[Route('/bonus-mensuels', name: 'app_recompense_bonus_mensuels', methods: ['GET'])]
    public function bonusMensuels(RecompenseRepository $recompenseRepository): Response
    {
        $recompenses = $recompenseRepository->createQueryBuilder('r')
            ->leftJoin('r.livreur', 'l')
            ->leftJoin('r.facture', 'f')
            ->addSelect('l', 'f')
            ->where('r.type = :type')
            ->setParameter('type', 'Bonus Mensuel')
            ->orderBy('r.dateObtention', 'DESC')
            ->getQuery()->getResult();

        return $this->render('admin/recompenses/bonus_mensuels.html.twig', [
            'recompenses' => $recompenses,
        ]);
    }

    #[Route('/primes-performance', name: 'app_recompense_primes_performance', methods: ['GET'])]
    public function primesPerformance(RecompenseRepository $recompenseRepository): Response
    {
        $recompenses = $recompenseRepository->createQueryBuilder('r')
            ->leftJoin('r.livreur', 'l')
            ->leftJoin('r.facture', 'f')
            ->addSelect('l', 'f')
            ->where('r.type = :type')
            ->setParameter('type', 'Prime de Performance')
            ->orderBy('r.dateObtention', 'DESC')
            ->getQuery()->getResult();

        return $this->render('admin/recompenses/primes_performance.html.twig', [
            'recompenses' => $recompenses,
        ]);
    }

    #[Route('/{id}/qrcode', name: 'app_recompense_qrcode', methods: ['GET'])]
    public function qrcode(Recompense $recompense): Response
    {
        $contenu = sprintf(
            "Récompense #%d\nType: %s\nLivreur: %s %s\nValeur: %.2f DT\nDate: %s",
            $recompense->getId(),
            $recompense->getType(),
            $recompense->getLivreur()->getPrenom(),
            $recompense->getLivreur()->getNom(),
            $recompense->getValeur(),
            $recompense->getDateObtention()->format('d/m/Y')
        );

        $qrCode = new QrCode($contenu);
        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg  = $result->getString();

        return $this->render('admin/recompenses/qrcode.html.twig', [
            'recompense' => $recompense,
            'qrSvg'      => $qrSvg,
        ]);
    }

    #[Route('/{id}', name: 'app_recompense_show', methods: ['GET'])]
    public function show(?Recompense $recompense): Response
    {
        if (!$recompense) {
            $this->addFlash('error', 'La récompense demandée n\'existe pas.');
            return $this->redirectToRoute('app_recompense_index');
        }

        return $this->render('admin/recompenses/show.html.twig', [
            'recompense' => $recompense,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_recompense_edit', methods: ['POST'])]
    public function edit(
        Request $request,
        Recompense $recompense,
        EntityManagerInterface $entityManager,
        UtilisateursRepository $utilisateurRepository,
        FactureRepository $factureRepository
    ): Response {
        if ($recompense->getType() === 'Commission' || $recompense->getType() === 'Bonus Mensuel') {
            $this->addFlash('error', '⛔ Les récompenses automatiques ne peuvent pas être modifiées.');
            return $this->redirectToRoute('app_recompense_index');
        }

        $type        = $request->request->get('type');
        $valeur      = $request->request->get('valeur');
        $description = $request->request->get('description');
        $seuil       = $request->request->get('seuil');
        $livreurId   = $request->request->get('livreur');
        $factureId   = $request->request->get('facture');

        if ($type === 'Commission' || $type === 'Bonus Mensuel') {
            $this->addFlash('error', '⛔ Impossible de changer le type vers une récompense automatique.');
            return $this->redirectToRoute('app_recompense_index');
        }

        $livreur = $utilisateurRepository->find($livreurId);

        if (!$livreur || $livreur->getRole() !== Role::LIVREUR) {
            $this->addFlash('error', 'Livreur invalide.');
            return $this->redirectToRoute('app_recompense_index');
        }

        $facture = null;
        if ($factureId) {
            $facture = $factureRepository->find($factureId);
        }

        $recompense->setType($type);
        $recompense->setValeur((float)$valeur);
        $recompense->setDescription($description);
        $recompense->setSeuil($seuil ? (int)$seuil : null);
        $recompense->setLivreur($livreur);
        $recompense->setFacture($facture);

        try {
            $entityManager->flush();
            $this->addFlash('success', '✅ Récompense modifiée avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', '❌ Erreur : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_recompense_index');
    }

    #[Route('/{id}/delete', name: 'app_recompense_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Recompense $recompense,
        EntityManagerInterface $entityManager
    ): Response {
        if ($recompense->getType() === 'Commission' || $recompense->getType() === 'Bonus Mensuel') {
            $this->addFlash('error', '⛔ Les récompenses automatiques ne peuvent pas être supprimées.');
            return $this->redirectToRoute('app_recompense_index');
        }

        if ($this->isCsrfTokenValid('delete' . $recompense->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($recompense);
                $entityManager->flush();
                $this->addFlash('success', '✅ Récompense supprimée avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ Erreur lors de la suppression : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_recompense_index');
    }
}