<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class VehicleController extends AbstractController
{
    #[Route('/vehicles', name: 'admin_vehicles')]
    public function index(): Response
    {
        return $this->render('admin/vehicles/index.html.twig');
    }
}