<?php

namespace App\Controller;

use App\Entity\Utilisateurs;
use App\Form\UtilisateursType;
use App\Repository\UtilisateursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\FileUploader;
use Knp\Component\Pager\PaginatorInterface;

final class UtilisateursController extends AbstractController
{
    // Afficher la liste de tous les utilisateurs avec pagination
    #[Route('/admin/utilisateurs', name: 'app_utilisateurs')]
    public function index(UtilisateursRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $repository->createQueryBuilder('u')->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5 // 5 utilisateurs par page
        );

        return $this->render('admin/utilisateurs/acceuil.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    // Afficher le formulaire et créer un nouvel utilisateur
    #[Route('/admin/ajouterutilisateur', name: 'app_utilisateurs_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, FileUploader $fileUploader): Response
    {
        $utilisateur = new Utilisateurs();
        $form = $this->createForm(UtilisateursType::class, $utilisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $utilisateur->setMotDePasse($passwordHasher->hashPassword($utilisateur, $plainPassword));

            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $utilisateur->setPhoto($fileUploader->upload($photoFile));
            }

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès !');
            return $this->redirectToRoute('app_utilisateurs');
        }

        return $this->render('admin/utilisateurs/ajouterutilisateur.html.twig', [
            'form' => $form,
            'submitted' => $form->isSubmitted(),
        ]);
    }

    // Afficher les détails d'un utilisateur
    #[Route('/admin/afficherutilisateur/{id}', name: 'app_utilisateurs_show')]
    public function show(Utilisateurs $utilisateur): Response
    {
        return $this->render('admin/utilisateurs/afficherutilisateurs.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    // Modifier un utilisateur
    #[Route('/admin/modifierutilisateur/{id}', name: 'app_utilisateurs_edit')]
    public function edit(Request $request, Utilisateurs $utilisateur, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(UtilisateursType::class, $utilisateur, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $utilisateur->setMotDePasse($passwordHasher->hashPassword($utilisateur, $plainPassword));
            }

            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                if ($utilisateur->getPhoto()) {
                    $fileUploader->delete($utilisateur->getPhoto());
                }
                $utilisateur->setPhoto($fileUploader->upload($photoFile));
            }

            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès !');
            return $this->redirectToRoute('app_utilisateurs');
        }

        return $this->render('admin/utilisateurs/modifierutilisateur.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
            'submitted' => $form->isSubmitted(),
        ]);
    }

    // Supprimer un utilisateur
    #[Route('/admin/supprimerutilisateur/{id}', name: 'app_utilisateurs_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateurs $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $utilisateur->getId(), $request->request->get('_token'))) {
            $entityManager->remove($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès !');
        }

        return $this->redirectToRoute('app_utilisateurs');
    }

    // Afficher les statistiques des utilisateurs
    #[Route('/admin/utilisateurs/statistiques', name: 'app_utilisateurs_stats')]
    public function stats(UtilisateursRepository $repository): Response
    {
        $utilisateurs = $repository->findAll();

        return $this->render('admin/utilisateurs/statsutilisateurs.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    // ===== EXPORT CSV =====
    #[Route('/admin/utilisateurs/export/csv', name: 'app_utilisateurs_export_csv')]
    public function exportCsv(UtilisateursRepository $repository): StreamedResponse
    {
        $utilisateurs = $repository->findAll();

        $response = new StreamedResponse(function () use ($utilisateurs) {
            $handle = fopen('php://output', 'w');

            // BOM pour Excel (UTF-8)
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // En-têtes CSV
            fputcsv($handle, ['ID', 'Nom', 'Prénom', 'Email', 'Téléphone', 'Rôle', 'Membre depuis'], ';');

            foreach ($utilisateurs as $u) {
                fputcsv($handle, [
                    $u->getId(),
                    $u->getNom(),
                    $u->getPrenom(),
                    $u->getEmail(),
                    $u->getTelephone() ?? 'Non renseigné',
                    $u->getRole()->value,
                    $u->getCreatedAt()->format('d/m/Y'),
                ], ';');
            }

            fclose($handle);
        });

        $filename = 'utilisateurs_' . date('Y-m-d') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    // ===== EXPORT PDF =====
    #[Route('/admin/utilisateurs/export/pdf', name: 'app_utilisateurs_export_pdf')]
    public function exportPdf(UtilisateursRepository $repository): Response
    {
        $utilisateurs = $repository->findAll();

        return $this->render('admin/utilisateurs/export_pdf.html.twig', [
            'utilisateurs' => $utilisateurs,
            'date' => new \DateTime(),
        ]);
    }
}