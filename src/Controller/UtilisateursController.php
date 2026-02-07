<?php

namespace App\Controller;

use App\Entity\Utilisateurs;
use App\Form\UtilisateursType;
use App\Repository\UtilisateursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UtilisateursController extends AbstractController
{
    // Afficher la liste de tous les utilisateurs
    #[Route('/utilisateurs', name: 'app_utilisateurs')]
    public function index(UtilisateursRepository $repository): Response
    {
        $utilisateurs = $repository->findAll();
        
        return $this->render('admin/utilisateurs/acceuil.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    // Afficher le formulaire et créer un nouvel utilisateur
    #[Route('/ajouterutilisateur', name: 'app_utilisateurs_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $utilisateur = new Utilisateurs();
        $form = $this->createForm(UtilisateursType::class, $utilisateur);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe avant de sauvegarder
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
            $utilisateur->setMotDePasse($hashedPassword);

            // Sauvegarder dans la base de données
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Utilisateur créé avec succès !');

            // Rediriger vers la liste
            return $this->redirectToRoute('app_utilisateurs');
        }

        // Afficher toujours le formulaire avec les erreurs s'il y en a
        return $this->render('admin/utilisateurs/ajouterutilisateur.html.twig', [
            'form' => $form,
            'submitted' => $form->isSubmitted(),
        ]);
    }

    // Afficher les détails d'un utilisateur
    #[Route('/afficherutilisateur/{id}', name: 'app_utilisateurs_show')]
    public function show(Utilisateurs $utilisateur): Response
    {
        return $this->render('admin/utilisateurs/afficherutilisateurs.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    // Modifier un utilisateur
    #[Route('/modifierutilisateur/{id}', name: 'app_utilisateurs_edit')]
    public function edit(Request $request, Utilisateurs $utilisateur, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UtilisateursType::class, $utilisateur, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si un nouveau mot de passe est fourni, le hasher
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
                $utilisateur->setMotDePasse($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès !');

            return $this->redirectToRoute('app_utilisateurs');
        }

        // Afficher toujours le formulaire avec les erreurs s'il y en a
        return $this->render('admin/utilisateurs/modifierutilisateur.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
            'submitted' => $form->isSubmitted(),
        ]);
    }

    // Supprimer un utilisateur
    #[Route('/supprimerutilisateur/{id}', name: 'app_utilisateurs_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateurs $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $entityManager->remove($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès !');
        }

        return $this->redirectToRoute('app_utilisateurs');
    }
}