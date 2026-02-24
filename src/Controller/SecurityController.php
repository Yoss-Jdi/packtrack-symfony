<?php

namespace App\Controller;

use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Utilisateurs;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // Si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            // Vérifier si l'utilisateur est admin
            if ($this->isGranted('ROLE_ADMIN')) {
                // TOUJOURS rediriger vers le modal de choix pour les admins
                return $this->redirectToRoute('admin_redirect_choice');
            } else {
                // Pour les autres rôles, rediriger vers le site normal
                return $this->redirectToRoute('app_home');
            }
        }

        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/admin/redirect-choice', name: 'admin_redirect_choice')]
    public function adminRedirectChoice(): Response
    {
        // Vérifier si l'utilisateur est connecté et admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('security/admin_redirect_choice.html.twig');
    }

    #[Route('/admin/choose-redirect', name: 'admin_choose_redirect')]
    public function chooseRedirect(Request $request): Response
    {
        // Vérifier si l'utilisateur est connecté et admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $choice = $request->query->get('choice', 'dashboard');
        
        // Rediriger selon le choix de l'admin
        if ($choice === 'dashboard') {
            return $this->redirectToRoute('admin_dashboard');
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): Response
    {
        // Récupérer les données du formulaire
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $confirmPassword = $request->request->get('confirm_password');
        $nom = $request->request->get('nom');
        $prenom = $request->request->get('prenom');
        $telephone = $request->request->get('telephone');

        // Validation basique
        $errors = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide';
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une lettre majuscule';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Les mots de passe ne correspondent pas';
        }

        if (empty($nom) || strlen($nom) < 3) {
            $errors[] = 'Le nom doit contenir au moins 3 caractères';
        }

        if (empty($prenom) || strlen($prenom) < 2) {
            $errors[] = 'Le prénom doit contenir au moins 2 caractères';
        }

        if (strtolower($nom) === strtolower($prenom)) {
            $errors[] = 'Le prénom doit être différent du nom';
        }

        if (empty($telephone) || !preg_match('/^[0-9]{8}$/', $telephone)) {
            $errors[] = 'Le numéro de téléphone doit contenir exactement 8 chiffres';
        }

        // Vérifier si l'email existe déjà
        $existingUser = $entityManager->getRepository(Utilisateurs::class)->findOneBy(['Email' => $email]);
        if ($existingUser) {
            $errors[] = 'Cet email est déjà utilisé';
        }

        // Si des erreurs existent, retourner au formulaire
        if (!empty($errors)) {
            return $this->render('security/login.html.twig', [
                'registration_errors' => $errors,
                'registration_data' => [
                    'email' => $email,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'telephone' => $telephone,
                ],
                'error' => null,
                'last_username' => '',
            ]);
        }

        // Créer le nouvel utilisateur
        $utilisateur = new Utilisateurs();
        $utilisateur->setEmail($email);
        $utilisateur->setNom($nom);
        $utilisateur->setPrenom($prenom);
        $utilisateur->setTelephone($telephone);
        $utilisateur->setRole(Role::CLIENT); // Par défaut, les nouveaux utilisateurs sont des clients // Par défaut, les nouveaux utilisateurs sont des clients
        
        // Hasher le mot de passe
        $hashedPassword = $passwordHasher->hashPassword($utilisateur, $password);
        $utilisateur->setMotDePasse($hashedPassword);

        // Sauvegarder
        $entityManager->persist($utilisateur);
        $entityManager->flush();

        // Message de succès
        $this->addFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');

        return $this->redirectToRoute('app_login');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode peut rester vide - Symfony gère la déconnexion automatiquement
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}