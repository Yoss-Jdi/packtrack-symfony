<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\Utilisateurs;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->isGranted('ROLE_ADMIN')
                ? $this->redirectToRoute('admin_redirect_choice')
                : $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * Route AJAX : vérifie email + mot de passe SANS créer de session.
     */
    #[Route('/login/check-credentials', name: 'app_login_check_credentials', methods: ['POST'])]
    public function checkCredentials(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data     = json_decode($request->getContent(), true);
        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            return $this->json(['success' => false, 'error' => 'Champs manquants'], 400);
        }

        /** @var Utilisateurs|null $user */
        $user = $em->getRepository(Utilisateurs::class)->findOneBy(['Email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['success' => false, 'error' => 'Email ou mot de passe incorrect']);
        }

        // Mémoriser l'email vérifié en session pour sécuriser la suite
        $request->getSession()->set('pending_login_email', $email);

        $hasPhoto = !empty($user->getPhoto());
        $photoUrl = $hasPhoto
            ? $request->getSchemeAndHttpHost() . '/uploads/profils/' . $user->getPhoto()
            : null;

        return $this->json([
            'success'  => true,
            'hasPhoto' => $hasPhoto,
            'photoUrl' => $photoUrl,
            'userName' => $user->getPrenom() . ' ' . $user->getNom(),
        ]);
    }

    #[Route('/admin/redirect-choice', name: 'admin_redirect_choice')]
    public function adminRedirectChoice(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('security/admin_redirect_choice.html.twig');
    }

    #[Route('/admin/choose-redirect', name: 'admin_choose_redirect')]
    public function chooseRedirect(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $request->query->get('choice') === 'dashboard'
            ? $this->redirectToRoute('admin_dashboard')
            : $this->redirectToRoute('app_home');
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        FileUploader $fileUploader
    ): Response {
        $email           = $request->request->get('email');
        $password        = $request->request->get('password');
        $confirmPassword = $request->request->get('confirm_password');
        $nom             = $request->request->get('nom');
        $prenom          = $request->request->get('prenom');
        $telephone       = $request->request->get('telephone');

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

        $existingUser = $entityManager->getRepository(Utilisateurs::class)->findOneBy(['Email' => $email]);
        if ($existingUser) {
            $errors[] = 'Cet email est déjà utilisé';
        }

        if (!empty($errors)) {
            return $this->render('security/login.html.twig', [
                'registration_errors' => $errors,
                'registration_data'   => compact('email', 'nom', 'prenom', 'telephone'),
            ]);
        }

        // Créer l'utilisateur
        $utilisateur = new Utilisateurs();
        $utilisateur->setEmail($email);
        $utilisateur->setNom($nom);
        $utilisateur->setPrenom($prenom);
        $utilisateur->setTelephone($telephone);
        $utilisateur->setRole(Role::CLIENT);
        $utilisateur->setMotDePasse($passwordHasher->hashPassword($utilisateur, $password));

        // Traiter la photo si uploadée
        $photoFile = $request->files->get('photo');
        if ($photoFile) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (in_array($photoFile->getMimeType(), $allowed) && $photoFile->getSize() <= 5 * 1024 * 1024) {
                $fileName = $fileUploader->upload($photoFile);
                $utilisateur->setPhoto($fileName);
            }
        }

        $entityManager->persist($utilisateur);
        $entityManager->flush();

        // Si l'utilisateur n'a pas uploadé de photo → rediriger vers setup photo
        if (!$utilisateur->getPhoto()) {
            $request->getSession()->set('setup_photo_email', $email);
            $this->addFlash('success', 'Compte créé ! Veuillez configurer votre photo de profil pour l\'authentification faciale.');
            return $this->redirectToRoute('app_setup_photo');
        }

        $this->addFlash('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Intercepted by firewall.');
    }
}
