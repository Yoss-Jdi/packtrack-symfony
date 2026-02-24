<?php

namespace App\Controller;

use App\Entity\Utilisateurs;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ProfilController extends AbstractController
{
    /**
     * Page de setup photo après inscription (ou quand l'user n'a pas de photo).
     * Accessible sans être connecté si on vient de l'inscription ou du login facial.
     */
    #[Route('/setup-photo', name: 'app_setup_photo')]
    public function setupPhoto(Request $request): Response
    {
        $session = $request->getSession();

        // Contexte : vient de l'inscription
        $email = $session->get('setup_photo_email');

        // Contexte : vient du login facial (no photo)
        if (!$email) {
            $email = $session->get('pending_login_email');
        }

        if (!$email) {
            // Si pas de contexte session ET utilisateur connecté → page profil normale
            if ($this->getUser()) {
                $email = $this->getUser()->getUserIdentifier();
            } else {
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('profil/setup_photo.html.twig', [
            'email'   => $email,
            'context' => $session->has('setup_photo_email') ? 'register' : 'login',
        ]);
    }

    /**
     * Route AJAX : sauvegarde la photo (base64 webcam ou fichier uploadé).
     * Fonctionne pour utilisateurs connectés ET non connectés (via session).
     */
    #[Route('/setup-photo/save', name: 'app_setup_photo_save', methods: ['POST'])]
    public function savePhoto(
        Request $request,
        EntityManagerInterface $em,
        FileUploader $fileUploader
    ): JsonResponse {
        $session = $request->getSession();

        // Trouver l'utilisateur via la session ou la connexion
        $email = $session->get('setup_photo_email')
            ?? $session->get('pending_login_email')
            ?? ($this->getUser() ? $this->getUser()->getUserIdentifier() : null);

        if (!$email) {
            return $this->json(['success' => false, 'error' => 'Session expirée'], 401);
        }

        /** @var Utilisateurs|null $user */
        $user = $em->getRepository(Utilisateurs::class)->findOneBy(['Email' => $email]);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
        }

        // Supprimer l'ancienne photo si elle existe
        if ($user->getPhoto()) {
            $fileUploader->delete($user->getPhoto());
        }

        $fileName = null;

        // Cas 1 : photo prise par webcam (base64)
        $base64 = $request->request->get('photo_base64');
        if ($base64) {
            try {
                $fileName = $fileUploader->uploadBase64($base64);
            } catch (\Exception $e) {
                return $this->json(['success' => false, 'error' => 'Erreur traitement image'], 500);
            }
        }

        // Cas 2 : fichier uploadé
        $uploadedFile = $request->files->get('photo_file');
        if ($uploadedFile && !$fileName) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($uploadedFile->getMimeType(), $allowed)) {
                return $this->json(['success' => false, 'error' => 'Format non autorisé (JPG, PNG, WEBP)']);
            }
            if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
                return $this->json(['success' => false, 'error' => 'Image trop lourde (max 5 Mo)']);
            }
            $fileName = $fileUploader->upload($uploadedFile);
        }

        if (!$fileName) {
            return $this->json(['success' => false, 'error' => 'Aucune photo reçue']);
        }

        $user->setPhoto($fileName);
        $em->flush();

        // Nettoyer la session d'inscription
        $session->remove('setup_photo_email');

        return $this->json([
            'success'  => true,
            'photoUrl' => '/uploads/profils/' . $fileName,
        ]);
    }

    /**
     * Page profil de l'utilisateur connecté (back office admin).
     */
    #[Route('/profil', name: 'app_profil')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('profil/index.html.twig');
    }

    /**
     * Page profil de l'utilisateur connecté (front office).
     */
    #[Route('/mon-profil', name: 'app_profil_front')]
    public function profilFront(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('profil/front.html.twig');
    }

    /**
     * Mise à jour photo depuis le profil (utilisateur connecté).
     */
    #[Route('/profil/photo', name: 'app_profil_photo', methods: ['POST'])]
    public function updatePhoto(
        Request $request,
        EntityManagerInterface $em,
        FileUploader $fileUploader
    ): JsonResponse {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var Utilisateurs $user */
        $user = $this->getUser();

        if ($user->getPhoto()) {
            $fileUploader->delete($user->getPhoto());
        }

        $fileName = null;

        $base64 = $request->request->get('photo_base64');
        if ($base64) {
            $fileName = $fileUploader->uploadBase64($base64);
        }

        $uploadedFile = $request->files->get('photo_file');
        if ($uploadedFile && !$fileName) {
            $fileName = $fileUploader->upload($uploadedFile);
        }

        if (!$fileName) {
            return $this->json(['success' => false, 'error' => 'Aucune photo reçue']);
        }

        $user->setPhoto($fileName);
        $em->flush();

        return $this->json([
            'success'  => true,
            'photoUrl' => '/uploads/profils/' . $fileName,
        ]);
    }

    /**
     * Mise à jour des coordonnées personnelles (nom, prénom, téléphone, mot de passe).
     */
    #[Route('/profil/update-info', name: 'app_profil_update_info', methods: ['POST'])]
    public function updateInfo(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var Utilisateurs $user */
        $user = $this->getUser();

        $nom     = trim($request->request->get('nom', ''));
        $prenom  = trim($request->request->get('prenom', ''));
        $tel     = trim($request->request->get('telephone', ''));
        $pwdOld  = $request->request->get('current_password', '');
        $pwdNew  = $request->request->get('new_password', '');
        $pwdConf = $request->request->get('confirm_password', '');

        $errors = [];

        // Validation nom
        if (strlen($nom) < 3) {
            $errors[] = 'Le nom doit contenir au moins 3 caractères.';
        }

        // Validation prénom
        if (strlen($prenom) < 2) {
            $errors[] = 'Le prénom doit contenir au moins 2 caractères.';
        }

        if ($nom && $prenom && strtolower($nom) === strtolower($prenom)) {
            $errors[] = 'Le prénom doit être différent du nom.';
        }

        // Validation téléphone
        if ($tel !== '' && !preg_match('/^[0-9]{8}$/', $tel)) {
            $errors[] = 'Le téléphone doit contenir exactement 8 chiffres.';
        }

        // Validation mot de passe (seulement si l'utilisateur veut en changer)
        if ($pwdNew !== '') {
            if (!$passwordHasher->isPasswordValid($user, $pwdOld)) {
                $errors[] = 'Le mot de passe actuel est incorrect.';
            }
            if (strlen($pwdNew) < 6) {
                $errors[] = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
            }
            if (!preg_match('/[A-Z]/', $pwdNew)) {
                $errors[] = 'Le nouveau mot de passe doit contenir au moins une majuscule.';
            }
            if (!preg_match('/[0-9]/', $pwdNew)) {
                $errors[] = 'Le nouveau mot de passe doit contenir au moins un chiffre.';
            }
            if ($pwdNew !== $pwdConf) {
                $errors[] = 'Les nouveaux mots de passe ne correspondent pas.';
            }
        }

        if (!empty($errors)) {
            return $this->json(['success' => false, 'errors' => $errors]);
        }

        // Appliquer les modifications
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setTelephone($tel ?: null);

        if ($pwdNew !== '') {
            $user->setMotDePasse($passwordHasher->hashPassword($user, $pwdNew));
        }

        $em->flush();

        return $this->json([
            'success'   => true,
            'message'   => 'Vos informations ont été mises à jour avec succès.',
            'prenom'    => $user->getPrenom(),
            'nom'       => $user->getNom(),
            'telephone' => $user->getTelephone(),
        ]);
    }
}