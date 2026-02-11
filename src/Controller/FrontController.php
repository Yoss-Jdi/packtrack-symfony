<?php

namespace App\Controller; // doit correspondre au dossier

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FactureRepository;

class FrontController extends AbstractController
{
    #[Route('/factures', name: 'front_factures')]
    public function index(Request $request, FactureRepository $factureRepository): Response
    {
        $numero = $request->query->get('numero');
        $facture = null;

        if ($numero) {
            $facture = $factureRepository->findOneBy(['numero' => $numero]);
        }

        return $this->render('front/home/factures.html.twig', [
            'facture' => $facture,
            'numero' => $numero,
        ]);
    }
}
