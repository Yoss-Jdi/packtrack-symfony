<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Utilisateurs;
use App\Entity\Colis;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        
    }

    /**
     * Créer une notification
     */
    public function creer(
        Utilisateurs $utilisateur,
        string $message,
        string $type = 'info',
        ?Colis $colis = null
    ): Notification {
        $notification = new Notification();
        $notification->setUtilisateur($utilisateur);
        $notification->setMessage($message);
        $notification->setType($type);
        $notification->setColis($colis);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    /**
     * Notifier lors d'un changement de statut de colis
     */
    public function notifierChangementStatut(Colis $colis, string $ancienStatut, string $nouveauStatut): void
    {
        $expediteur = $colis->getExpediteur();
        $destinataire = $colis->getDestinataire();

        // Notification selon le nouveau statut
        switch ($nouveauStatut) {
            case 'en_cours':
                // Notifier l'expéditeur
                $this->creer(
                    $expediteur,
                    "📦 Votre colis #{$colis->getId()} a été pris en charge par un livreur.",
                    'info',
                    $colis
                );
                
                // Notifier le destinataire
                $this->creer(
                    $destinataire,
                    "📦 Un colis vous est destiné et est en cours de livraison (Colis #{$colis->getId()}).",
                    'info',
                    $colis
                );
                break;

            case 'livre':
                // Notifier l'expéditeur
                $this->creer(
                    $expediteur,
                    "✅ Votre colis #{$colis->getId()} a été livré avec succès !",
                    'success',
                    $colis
                );
                
                // Notifier le destinataire
                $this->creer(
                    $destinataire,
                    "✅ Votre colis #{$colis->getId()} a été livré. Bon réception !",
                    'success',
                    $colis
                );
                break;
        }
    }

    /**
     * Notifier l'admin des colis en attente depuis longtemps
     */
    public function notifierColisEnAttente(Utilisateurs $admin, array $colisEnAttente): void
    {
        if (empty($colisEnAttente)) {
            return;
        }

        $nbColis = count($colisEnAttente);
        $message = "⚠️ Attention : $nbColis colis " . ($nbColis > 1 ? 'sont' : 'est') . 
                   " en attente depuis plus de 3 jours et " . ($nbColis > 1 ? 'n\'ont' : 'n\'a') . 
                   " pas encore été " . ($nbColis > 1 ? 'pris' : 'pris') . " en charge.";

        $this->creer($admin, $message, 'warning');
    }

    /**
     * Marquer une notification comme lue
     */
    public function marquerCommeLue(Notification $notification): void
    {
        $notification->setLu(true);
        $this->entityManager->flush();
    }
}
