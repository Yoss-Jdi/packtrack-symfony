<?php

namespace App\Repository;

use App\Entity\Recompense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RecompenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recompense::class);
    }

    /**
     * 📊 Récupérer la répartition des récompenses par type
     */
    public function getRepartitionParType(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.type, COUNT(r.id) as nombre, SUM(r.valeur) as total')
            ->groupBy('r.type')
            ->orderBy('nombre', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 💰 Calculer le total de la valeur des récompenses
     */
    public function getTotalValeur(): float
    {
        $result = $this->createQueryBuilder('r')
            ->select('SUM(r.valeur) as total')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float)$result : 0.0;
    }

    /**
     * 🏆 Récupérer le top des livreurs par nombre de récompenses
     */
    public function getTopLivreurs(int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->select('u.nom, u.prenom, COUNT(r.id) as nombreRecompenses, SUM(r.valeur) as totalValeur')
            ->join('r.livreur', 'u')
            ->groupBy('u.id')
            ->orderBy('nombreRecompenses', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * 📅 Récupérer les récompenses d'un livreur
     */
    public function findByLivreur(int $livreurId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.livreur = :livreur')
            ->setParameter('livreur', $livreurId)
            ->orderBy('r.dateObtention', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 📈 Récupérer les récompenses par période
     */
    public function findByPeriode(\DateTime $debut, \DateTime $fin): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.dateObtention BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('r.dateObtention', 'DESC')
            ->getQuery()
            ->getResult();
    }
}