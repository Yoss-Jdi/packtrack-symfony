<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class ParcelController extends AbstractController
{
    #[Route('/parcels', name: 'admin_parcels')]
    public function index(): Response
    {
        return $this->render('admin/parcels/index.html.twig');
    }
}