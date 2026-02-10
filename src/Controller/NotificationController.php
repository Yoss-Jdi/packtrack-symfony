<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifications')]
class NotificationController extends AbstractController
{
    #[Route('/', name: 'app_notifications_index', methods: ['GET'])]
    public function index(NotificationRepository $notificationRepository): Response
    {
        // NOTE: Les contrôles liés à l'utilisateur connecté sont commentés pour les tests.
        // Récupérer les notifications de l'utilisateur connecté
        // $user = $this->getUser();
        // 
        // if (!$user) {
        //     throw $this->createAccessDeniedException('Vous devez être connecté');
        // }

        // Récupération normale : notifications pour l'utilisateur
        // $notifications = $notificationRepository->findBy(
        //     ['utilisateur' => $user],
        //     ['dateCreation' => 'DESC']
        // );

        // Compter les notifications non lues pour l'utilisateur
        // $nonLues = $notificationRepository->findBy(
        //     ['utilisateur' => $user, 'lu' => false],
        // );

        // Version de test : retourner toutes les notifications (pas de filtrage par utilisateur)
        $notifications = $notificationRepository->findBy([], ['dateCreation' => 'DESC']);
        $nonLues = $notificationRepository->findBy(['lu' => false]);

        return $this->render('front/notification/index.html.twig', [
            'notifications' => $notifications,
            'nbNonLues' => count($nonLues),
        ]);
    }

    #[Route('/{id}/mark-as-read', name: 'app_notification_mark_as_read', methods: ['POST'])]
    public function markAsRead(
        int $id,
        NotificationRepository $notificationRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // Contrôles liés à l'utilisateur connecté commentés pour les tests
        // $user = $this->getUser();
        // 
        // if (!$user) {
        //     throw $this->createAccessDeniedException('Vous devez être connecté');
        // }

        $notification = $notificationRepository->find($id);

        // Vérifier que la notification appartient à l'utilisateur (désactivé pour test)
        // if (!$notification || $notification->getUtilisateur() !== $user) {
        //     throw $this->createAccessDeniedException('Accès non autorisé');
        // }

        if ($notification) {
            $notification->setLu(true);
            $entityManager->flush();
        }

        $this->addFlash('success', 'Notification marquée comme lue (mode test)');

        return $this->redirectToRoute('app_notifications_index');
    }

    #[Route('/{id}/delete', name: 'app_notification_delete', methods: ['POST'])]
    public function delete(
        int $id,
        NotificationRepository $notificationRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // Contrôles liés à l'utilisateur connecté commentés pour les tests
        // $user = $this->getUser();
        // 
        // if (!$user) {
        //     throw $this->createAccessDeniedException('Vous devez être connecté');
        // }

        $notification = $notificationRepository->find($id);

        // Vérifier que la notification appartient à l'utilisateur (désactivé pour test)
        // if (!$notification || $notification->getUtilisateur() !== $user) {
        //     throw $this->createAccessDeniedException('Accès non autorisé');
        // }

        if ($notification && $this->isCsrfTokenValid('delete'.$notification->getId(), $request->request->get('_token'))) {
            $entityManager->remove($notification);
            $entityManager->flush();
            $this->addFlash('success', 'Notification supprimée (mode test)');
        }

        return $this->redirectToRoute('app_notifications_index');
    }

    #[Route('/mark-all-as-read', name: 'app_notifications_mark_all_as_read', methods: ['POST'])]
    public function markAllAsRead(
        NotificationRepository $notificationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Contrôles liés à l'utilisateur connecté commentés pour les tests
        // $user = $this->getUser();
        // 
        // if (!$user) {
        //     throw $this->createAccessDeniedException('Vous devez être connecté');
        // }

        // Version de test : marquer TOUTES les notifications non lues
        $notifications = $notificationRepository->findBy(['lu' => false]);

        foreach ($notifications as $notification) {
            $notification->setLu(true);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Toutes les notifications ont été marquées comme lues (mode test)');

        return $this->redirectToRoute('app_notifications_index');
    }

    #[Route('/test', name: 'app_notifications_test', methods: ['GET'])]
    public function test(NotificationRepository $notificationRepository): Response
    {
        // Route de test : retourne le nombre total de notifications
        $total = $notificationRepository->count([]);
        return new Response(sprintf('Mode test - notifications totales : %d', $total));
    }
}
