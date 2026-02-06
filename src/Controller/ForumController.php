<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class ForumController extends AbstractController
{
    #[Route('/forum', name: 'admin_forum')]
    public function index(): Response
    {
        return $this->render('admin/forum/index.html.twig');
    }
}