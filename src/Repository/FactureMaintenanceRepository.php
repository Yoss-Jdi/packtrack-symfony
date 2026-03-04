<?php

namespace App\Repository;

use App\Entity\FactureMaintenance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FactureMaintenanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FactureMaintenance::class);
    }

    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.dateEmission', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByVehicule(int $vehiculeId): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.vehicule = :vehiculeId')
            ->setParameter('vehiculeId', $vehiculeId)
            ->orderBy('f.dateEmission', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalMaintenanceCost(): float
    {
        $result = $this->createQueryBuilder('f')
            ->select('SUM(f.montantTTC) as total')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }
}
