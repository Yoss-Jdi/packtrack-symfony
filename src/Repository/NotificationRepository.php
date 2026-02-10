<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\Utilisateurs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Récupérer les notifications non lues d'un utilisateur
     */
    public function findNonLues(Utilisateurs $utilisateur): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.utilisateur = :user')
            ->andWhere('n.lu = false')
            ->setParameter('user', $utilisateur)
            ->orderBy('n.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter les notifications non lues
     */
    public function countNonLues(Utilisateurs $utilisateur): int
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.utilisateur = :user')
            ->andWhere('n.lu = false')
            ->setParameter('user', $utilisateur)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupérer toutes les notifications d'un utilisateur
     */
    public function findByUtilisateur(Utilisateurs $utilisateur, int $limit = 20): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.utilisateur = :user')
            ->setParameter('user', $utilisateur)
            ->orderBy('n.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function marquerToutesCommeLues(Utilisateurs $utilisateur): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.lu', true)
            ->where('n.utilisateur = :user')
            ->andWhere('n.lu = false')
            ->setParameter('user', $utilisateur)
            ->getQuery()
            ->execute();
    }
}