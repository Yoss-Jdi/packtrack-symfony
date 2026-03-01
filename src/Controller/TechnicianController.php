<?php

namespace App\Controller;

use App\Entity\Technician;
use App\Form\TechnicianType;
use App\Repository\TechnicianRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/technicians')]
class TechnicianController extends AbstractController
{
    #[Route('/', name: 'admin_technicians_index')]
    public function index(Request $request, TechnicianRepository $repository): Response
    {
        $search = $request->query->get('q');
        $sort = $request->query->get('sort');
        $direction = $request->query->get('direction', 'ASC');

        $technicians = $repository->searchAndSort($search, $sort, $direction);

        return $this->render('admin/technicians/index.html.twig', [
            'technicians' => $technicians,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'admin_technicians_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $technician = new Technician();
        $form = $this->createForm(TechnicianType::class, $technician);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($technician);
            $em->flush();

            $this->addFlash('success', 'Technicien créé avec succès.');

            return $this->redirectToRoute('admin_technicians_index');
        }

        return $this->render('admin/technicians/form.html.twig', [
            'form' => $form->createView(),
            'technician' => $technician,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_technicians_edit')]
    public function edit(Technician $technician, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TechnicianType::class, $technician);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Technicien mis à jour avec succès.');

            return $this->redirectToRoute('admin_technicians_index');
        }

        return $this->render('admin/technicians/form.html.twig', [
            'form' => $form->createView(),
            'technician' => $technician,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_technicians_delete', methods: ['POST'])]
    public function delete(Technician $technician, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_technician_' . $technician->getId(), (string) $request->request->get('_token'))) {
            $em->remove($technician);
            $em->flush();
            $this->addFlash('success', 'Technicien supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_technicians_index');
    }
}
