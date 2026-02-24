<?php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use App\Entity\Utilisateurs;
use App\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class PasswordResetController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(): Response
    {
        // Si l'utilisateur est déjà connecté, rediriger
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/forgot-password/send', name: 'app_forgot_password_send', methods: ['POST'])]
    public function sendResetCode(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        PasswordResetTokenRepository $tokenRepository,
        Environment $twig,
        LoggerInterface $logger = null
    ): Response {
        // Vérifier le token CSRF
        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('forgot_password', $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $emailAddress = $request->request->get('email');

        // Validation de l'email
        if (empty($emailAddress) || !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Veuillez entrer une adresse email valide.');
            return $this->redirectToRoute('app_forgot_password');
        }

        // Vérifier si l'utilisateur existe
        $utilisateur = $entityManager->getRepository(Utilisateurs::class)->findOneBy(['Email' => $emailAddress]);

        // Pour des raisons de sécurité, on affiche toujours le même message
        if ($utilisateur) {
            try {
                // Supprimer les anciens tokens pour cet email
                $tokenRepository->deleteOldTokensForEmail($emailAddress);

                // Créer un nouveau token avec un code à 6 chiffres
                $resetToken = new PasswordResetToken();
                $resetToken->setEmail($emailAddress);
                
                // Générer un code à 6 chiffres
                $code = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                $resetToken->setToken($code);
                
                // Le token expire dans 15 minutes
                $expiresAt = new \DateTime();
                $expiresAt->modify('+15 minutes');
                $resetToken->setExpiresAt($expiresAt);

                $entityManager->persist($resetToken);
                $entityManager->flush();

                // Préparer les données pour l'email
                $userName = $utilisateur->getPrenom() . ' ' . $utilisateur->getNom();

                // Rendu du template d'email
                $htmlContent = $twig->render('emails/reset_password_code_email.html.twig', [
                    'code' => $code,
                    'expiresAt' => $expiresAt,
                    'userName' => $userName
                ]);

                // Configuration de l'email depuis les variables d'environnement
                $fromAddress = $_ENV['MAILER_FROM_ADDRESS'] ?? 'no-reply@packtrack.com';
                $fromName = $_ENV['MAILER_FROM_NAME'] ?? 'PackTrack Support';

                // Log pour débogage
                if ($logger) {
                    $logger->info('Tentative d\'envoi d\'email avec code', [
                        'from' => $fromAddress,
                        'to' => $emailAddress,
                        'code' => $code
                    ]);
                }

                // Créer et envoyer l'email
                $email = (new Email())
                    ->from(new Address($fromAddress, $fromName))
                    ->to($emailAddress)
                    ->subject('Code de réinitialisation de mot de passe - PackTrack')
                    ->html($htmlContent);

                $mailer->send($email);
                
                if ($logger) {
                    $logger->info('Email avec code envoyé avec succès', ['to' => $emailAddress]);
                }

                // Stocker l'email dans la session pour la page de vérification
                $request->getSession()->set('reset_email', $emailAddress);
                
            } catch (\Exception $e) {
                // Logger l'erreur détaillée
                if ($logger) {
                    $logger->error('Erreur envoi email de réinitialisation', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                // Afficher l'erreur en développement
                if ($_ENV['APP_ENV'] === 'dev') {
                    $this->addFlash('error', 'Erreur d\'envoi d\'email: ' . $e->getMessage());
                } else {
                    $this->addFlash('error', 'Une erreur s\'est produite lors de l\'envoi de l\'email. Veuillez réessayer plus tard.');
                }
                return $this->redirectToRoute('app_forgot_password');
            }
        } else {
            // Même si l'utilisateur n'existe pas, on stocke l'email pour éviter de révéler son inexistence
            $request->getSession()->set('reset_email', $emailAddress);
        }

        // Rediriger vers la page de vérification du code
        return $this->redirectToRoute('app_verify_code');
    }

    #[Route('/verify-code', name: 'app_verify_code')]
    public function verifyCode(Request $request): Response
    {
        // Vérifier qu'on a un email en session
        $email = $request->getSession()->get('reset_email');
        
        if (!$email) {
            $this->addFlash('error', 'Session expirée. Veuillez recommencer.');
            return $this->redirectToRoute('app_forgot_password');
        }

        return $this->render('security/verify_code.html.twig', [
            'email' => $email
        ]);
    }

    #[Route('/verify-code/check', name: 'app_verify_code_check', methods: ['POST'])]
    public function checkCode(
        Request $request,
        PasswordResetTokenRepository $tokenRepository
    ): Response {
        // Vérifier le token CSRF
        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('verify_code', $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
            return $this->redirectToRoute('app_verify_code');
        }

        $email = $request->getSession()->get('reset_email');
        
        if (!$email) {
            $this->addFlash('error', 'Session expirée. Veuillez recommencer.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $code = $request->request->get('code');

        // Validation du code
        if (empty($code) || !preg_match('/^\d{6}$/', $code)) {
            $this->addFlash('error', 'Le code doit contenir exactement 6 chiffres.');
            return $this->redirectToRoute('app_verify_code');
        }

        // Vérifier le code
        $resetToken = $tokenRepository->findValidTokenByEmailAndCode($email, $code);

        if (!$resetToken) {
            $this->addFlash('error', 'Code invalide ou expiré. Veuillez réessayer ou demander un nouveau code.');
            return $this->redirectToRoute('app_verify_code');
        }

        // Code valide, stocker le token dans la session
        $request->getSession()->set('reset_token', $resetToken->getToken());

        // Rediriger vers la page de nouveau mot de passe
        return $this->redirectToRoute('app_new_password');
    }

    #[Route('/new-password', name: 'app_new_password')]
    public function newPassword(Request $request): Response
    {
        // Vérifier qu'on a un token validé en session
        $token = $request->getSession()->get('reset_token');
        
        if (!$token) {
            $this->addFlash('error', 'Session expirée. Veuillez recommencer.');
            return $this->redirectToRoute('app_forgot_password');
        }

        return $this->render('security/new_password.html.twig');
    }

    #[Route('/new-password/update', name: 'app_new_password_update', methods: ['POST'])]
    public function updatePassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        PasswordResetTokenRepository $tokenRepository
    ): Response {
        // Vérifier le token CSRF
        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('new_password', $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
            return $this->redirectToRoute('app_new_password');
        }

        $token = $request->getSession()->get('reset_token');
        
        if (!$token) {
            $this->addFlash('error', 'Session expirée. Veuillez recommencer.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $password = $request->request->get('password');
        $confirmPassword = $request->request->get('confirm_password');

        // Vérifier le token
        $resetToken = $tokenRepository->findValidToken($token);

        if (!$resetToken) {
            $this->addFlash('error', 'Token invalide ou expiré. Veuillez recommencer.');
            $request->getSession()->remove('reset_token');
            $request->getSession()->remove('reset_email');
            return $this->redirectToRoute('app_forgot_password');
        }

        // Validation du mot de passe
        $errors = [];

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

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }
            return $this->redirectToRoute('app_new_password');
        }

        // Récupérer l'utilisateur
        $utilisateur = $entityManager->getRepository(Utilisateurs::class)
            ->findOneBy(['Email' => $resetToken->getEmail()]);

        if (!$utilisateur) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            $request->getSession()->remove('reset_token');
            $request->getSession()->remove('reset_email');
            return $this->redirectToRoute('app_forgot_password');
        }

        // Mettre à jour le mot de passe
        $hashedPassword = $passwordHasher->hashPassword($utilisateur, $password);
        $utilisateur->setMotDePasse($hashedPassword);

        // Marquer le token comme utilisé
        $resetToken->setUsed(true);

        $entityManager->flush();

        // Nettoyer la session
        $request->getSession()->remove('reset_token');
        $request->getSession()->remove('reset_email');

        // Nettoyer les tokens expirés (tâche de maintenance)
        try {
            $tokenRepository->deleteExpiredTokens();
        } catch (\Exception $e) {
            // Ignorer les erreurs de nettoyage
        }

        $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès ! Vous pouvez maintenant vous connecter.');

        return $this->redirectToRoute('app_login');
    }

    #[Route('/resend-code', name: 'app_resend_code', methods: ['POST'])]
    public function resendCode(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        PasswordResetTokenRepository $tokenRepository,
        Environment $twig,
        LoggerInterface $logger = null
    ): Response {
        $email = $request->getSession()->get('reset_email');
        
        if (!$email) {
            $this->addFlash('error', 'Session expirée. Veuillez recommencer.');
            return $this->redirectToRoute('app_forgot_password');
        }

        // Vérifier si l'utilisateur existe
        $utilisateur = $entityManager->getRepository(Utilisateurs::class)->findOneBy(['Email' => $email]);

        if ($utilisateur) {
            try {
                // Supprimer les anciens tokens
                $tokenRepository->deleteOldTokensForEmail($email);

                // Créer un nouveau code
                $resetToken = new PasswordResetToken();
                $resetToken->setEmail($email);
                
                $code = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                $resetToken->setToken($code);
                
                $expiresAt = new \DateTime();
                $expiresAt->modify('+15 minutes');
                $resetToken->setExpiresAt($expiresAt);

                $entityManager->persist($resetToken);
                $entityManager->flush();

                // Envoyer l'email
                $userName = $utilisateur->getPrenom() . ' ' . $utilisateur->getNom();

                $htmlContent = $twig->render('emails/reset_password_code_email.html.twig', [
                    'code' => $code,
                    'expiresAt' => $expiresAt,
                    'userName' => $userName
                ]);

                $fromAddress = $_ENV['MAILER_FROM_ADDRESS'] ?? 'bargaouiyassine860@gmail.com';
                $fromName = $_ENV['MAILER_FROM_NAME'] ?? 'PackTrack Support';

                $email = (new Email())
                    ->from(new Address($fromAddress, $fromName))
                    ->to($email)
                    ->subject('Nouveau code de réinitialisation - PackTrack')
                    ->html($htmlContent);

                $mailer->send($email);

                $this->addFlash('success', 'Un nouveau code a été envoyé à votre adresse email.');
                
            } catch (\Exception $e) {
                if ($logger) {
                    $logger->error('Erreur renvoi code', [
                        'message' => $e->getMessage()
                    ]);
                }
                
                $this->addFlash('error', 'Erreur lors de l\'envoi du code. Veuillez réessayer.');
            }
        }

        return $this->redirectToRoute('app_verify_code');
    }

    /**
     * Route de maintenance pour nettoyer les tokens expirés (à appeler via cron)
     */
    #[Route('/admin/cleanup-tokens', name: 'app_cleanup_tokens')]
    public function cleanupTokens(PasswordResetTokenRepository $tokenRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $results = $tokenRepository->cleanupDatabase();

        $this->addFlash('success', sprintf(
            'Nettoyage effectué : %d tokens expirés et %d tokens utilisés supprimés (Total: %d)',
            $results['expired'],
            $results['used'],
            $results['total']
        ));

        return $this->redirectToRoute('app_utilisateurs');
    }
}