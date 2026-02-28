<?php

namespace App\Controller;
use App\Entity\Facture;
use App\Form\FactureType;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
#[Route('/facture')]
final class FactureController extends AbstractController
{
    #[Route(name: 'app_facture_index', methods: ['GET'])]
public function index(Request $request, FactureRepository $factureRepository): Response
{
    // Récupérer la valeur du champ de recherche
    $searchNumero = $request->query->get('numero');

    if ($searchNumero) {
        // Si un numéro est recherché, filtrer
        $factures = $factureRepository->findBy(['numero' => $searchNumero]);
    } else {
        $factures = $factureRepository->findAll();
    }

    return $this->render('admin/factures/index_new.html.twig', [
        'factures' => $factures,
        'searchNumero' => $searchNumero, // pour pré-remplir le champ dans Twig
    ]);
}

#[Route('/new', name: 'app_facture_new', methods: ['GET','POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, FactureRepository $factureRepository): Response
{
    $facture = new Facture();
    $form = $this->createForm(FactureType::class, $facture);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // Vérifier le format FAC-XXX
        if (!preg_match('/^FAC-\d{3}$/', $facture->getNumero())) {
            $this->addFlash('error', 'Le numéro de facture doit être au format FAC-XXX (ex: FAC-006).');
            return $this->render('admin/factures/new.html.twig', [
                'form' => $form->createView(),
                'facture' => $facture,
            ]);
        }

        // Vérifier si le numéro existe déjà
        $existingFacture = $factureRepository->findOneBy(['numero' => $facture->getNumero()]);
        if ($existingFacture) {
            $this->addFlash('error', 'Ce numéro de facture existe déjà.');
            return $this->render('admin/factures/new.html.twig', [
                'form' => $form->createView(),
                'facture' => $facture,
            ]);
        }

        // Calcul automatique TVA et Montant TTC
        $montantHT = $facture->getMontantHT() ?? 0;
        $facture->setTva($montantHT * 0.20);
        $facture->setMontantTTC($montantHT * 1.20);

        // Persister et gérer exception si doublon inattendu
        try {
            $entityManager->persist($facture);
            $entityManager->flush();
            $this->addFlash('success', 'Facture créée avec succès !');
            return $this->redirectToRoute('app_facture_index');
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->addFlash('error', 'Ce numéro de facture existe déjà (erreur base de données).');
            return $this->render('admin/factures/new.html.twig', [
                'form' => $form->createView(),
                'facture' => $facture,
            ]);
        }
    }

    return $this->render('admin/factures/new.html.twig', [
        'form' => $form->createView(),
        'facture' => $facture,
    ]);
}




#[Route('/{id}', name: 'app_facture_show', methods: ['GET'])]
public function show(?Facture $facture): Response
{
    if (!$facture) {
        $this->addFlash('error', 'La facture demandée n’existe pas.');
        return $this->redirectToRoute('app_facture_index');
    }

    return $this->render('admin/factures/show.html.twig', [
        'facture' => $facture,
    ]);
}
   #[Route('/{id}/edit', name: 'app_facture_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Facture $facture, EntityManagerInterface $entityManager): Response
{
    if ($request->isMethod('POST')) {
        // Récupérer les données du formulaire manuel
        $numero = $request->request->get('numero');
        $dateEmission = $request->request->get('dateEmission');
        $montantHT = $request->request->get('montantHT');

        // Mettre à jour l'entité
        $facture->setNumero($numero);
        $facture->setMontantHT((float)$montantHT);

        // TVA automatique 20%
        $facture->setTva($facture->getMontantHT() * 0.20);

        // Montant TTC automatique
        $facture->setMontantTTC($facture->getMontantHT() * (1 + $facture->getTva() / 100));

        if ($dateEmission) {
            $facture->setDateEmission(new \DateTime($dateEmission));
        } else {
            $facture->setDateEmission(null);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Facture modifiée avec succès !');

        return $this->redirectToRoute('app_facture_index');
    }

    return $this->render('admin/factures/edit.html.twig', [
        'facture' => $facture,
    ]);
}


    #[Route('/{id}', name: 'app_facture_delete', methods: ['POST'])]
    public function delete(Request $request, Facture $facture, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$facture->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($facture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_facture_index', [], Response::HTTP_SEE_OTHER);
    }

#[Route('/{id}/pdf', name: 'app_facture_pdf', methods: ['GET'])]
public function pdf(Facture $facture): Response
{
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);

    // Générer le HTML
    $html = $this->renderView('admin/factures/pdf.html.twig', [
        'facture' => $facture
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return new Response($dompdf->stream('facture-' . $facture->getNumero() . '.pdf', [
        'Attachment' => true
    ]));
}



}