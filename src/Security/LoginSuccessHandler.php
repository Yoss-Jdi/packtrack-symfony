<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        // Récupérer l'utilisateur connecté
        $user = $token->getUser();

        // Vérifier les rôles de l'utilisateur
        $roles = $user->getRoles();

        // Si l'utilisateur est ADMIN, toujours rediriger vers le modal de choix
        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse(
                $this->urlGenerator->generate('admin_redirect_choice')
            );
        }

        // Pour tous les autres utilisateurs, rediriger vers la page d'accueil
        return new RedirectResponse(
            $this->urlGenerator->generate('app_home')
        );
    }
}