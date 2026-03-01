<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class InvoiceController extends AbstractController
{
    #[Route('/invoices', name: 'admin_invoices')]
    public function index(): Response
    {
        return $this->render('admin/invoices/index.html.twig');
    }
}