<?php

namespace App\Repository;

use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facture::class);
    }

    public function searchByStatutEtPeriode(?string $statut, ?string $periode, ?string $dateSpecifique = null): array
    {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin('f.livraison', 'l')
            ->leftJoin('l.colis', 'c')
            ->leftJoin('c.destinataire', 'd')
            ->leftJoin('l.livreur', 'livreur')
            ->addSelect('l', 'c', 'd', 'livreur');

        if (!empty($statut)) {
            $qb->andWhere('f.statut = :statut')
               ->setParameter('statut', $statut);
        }

        $now = new \DateTime();

        if ($periode === 'mois_actuel') {
            $debut = (clone $now)->modify('first day of this month')->setTime(0, 0, 0);
            $fin   = (clone $now)->modify('last day of this month')->setTime(23, 59, 59);
            $qb->andWhere('f.dateEmission BETWEEN :debut AND :fin')
               ->setParameter('debut', $debut)
               ->setParameter('fin', $fin);

        } elseif ($periode === 'mois_dernier') {
            $debut = (clone $now)->modify('first day of last month')->setTime(0, 0, 0);
            $fin   = (clone $now)->modify('last day of last month')->setTime(23, 59, 59);
            $qb->andWhere('f.dateEmission BETWEEN :debut AND :fin')
               ->setParameter('debut', $debut)
               ->setParameter('fin', $fin);

        } elseif ($periode === 'date_specifique' && !empty($dateSpecifique)) {
            // Filtrer sur le jour exact choisi par l'admin (ex: 2024-11-15)
            $debut = new \DateTime($dateSpecifique);
            $debut->setTime(0, 0, 0);
            $fin = clone $debut;
            $fin->setTime(23, 59, 59);
            $qb->andWhere('f.dateEmission BETWEEN :debut AND :fin')
               ->setParameter('debut', $debut)
               ->setParameter('fin', $fin);
        }

        return $qb->orderBy('f.dateEmission', 'DESC')
                  ->getQuery()
                  ->getResult();
    }
}