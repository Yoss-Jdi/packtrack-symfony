<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class IncidentController extends AbstractController
{
    #[Route('/incidents', name: 'admin_incidents')]
    public function index(): Response
    {
        return $this->render('admin/incidents/index.html.twig');
    }
}