<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(): Response
    {
        // Vérifier si l'utilisateur est connecté et admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Afficher le tableau de bord admin
        return $this->render('admin/dashboard/index.html.twig');
    }
}