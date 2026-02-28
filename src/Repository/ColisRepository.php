<?php

namespace App\Repository;

use App\Entity\Colis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ColisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Colis::class);
    }

    // Récupérer les colis par statut
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Récupérer les colis disponibles pour livraison
    public function findColisDisponibles(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.statut IN (:statuts)')
            ->setParameter('statuts', ['en_attente', 'processing'])
            ->orderBy('c.dateCreation', 'ASC')
            ->getQuery()
            ->getResult();
    }


    //Récupère les colis qui n'ont pas encore de livraison assignée
     public function findColisSansLivraison(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.livraisons', 'l')
            ->where('l.id IS NULL')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', 'en_attente')
            ->orderBy('c.dateCreation', 'ASC')
            ->getQuery()
            ->getResult();
    }
}