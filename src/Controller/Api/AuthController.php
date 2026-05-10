<?php
// src/Controller/Api/AuthController.php

namespace App\Controller\Api;

use App\Repository\UtilisateursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class AuthController extends AbstractController
{
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UtilisateursRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return $this->json([
                'success' => false,
                'message' => 'Email et mot de passe requis'
            ], 400);
        }

        $user = $userRepository->findOneBy(['Email' => $email]);

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Identifiants invalides'
            ], 401);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json([
                'success' => false,
                'message' => 'Identifiants invalides'
            ], 401);
        }

        return $this->json([
            'success' => true,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'role' => $user->getRole(),
                'photo' => $user->getPhoto(),
                'telephone' => $user->getTelephone(),
                'mot_de_passe' => $user->getMotDePasse(), // Hash pour JavaFX
            ]
        ]);
    }
}