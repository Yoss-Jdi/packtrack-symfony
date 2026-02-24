<?php

namespace App\Repository;

use App\Entity\Livraison;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LivraisonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livraison::class);
    }

    // Récupérer les livraisons en cours
    public function findLivraisonsEnCours(): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.statut = :statut')
            ->setParameter('statut', 'en_cours')
            ->orderBy('l.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Récupérer les livraisons d'un livreur
    public function findByLivreur(int $livreurId): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.livreur', 'u')
            ->where('u.id = :livreurId')
            ->setParameter('livreurId', $livreurId)
            ->orderBy('l.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }
}