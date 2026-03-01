<?php

namespace App\Repository;

use App\Entity\Vehicule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicule>
 */
class VehiculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicule::class);
    }

    public function getSearchAndSortQueryBuilder(?string $term, ?string $sortField, ?string $direction): QueryBuilder
    {
        $qb = $this->createQueryBuilder('v')
            ->leftJoin('v.technician', 't')
            ->addSelect('t');

        if ($term) {
            $qb
                ->andWhere('v.marque LIKE :term OR v.modele LIKE :term OR v.immatriculation LIKE :term OR t.nom LIKE :term OR t.prenom LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        $allowedSorts = ['marque', 'modele', 'immatriculation', 'statut'];
        if ($sortField && \in_array($sortField, $allowedSorts, true)) {
            $direction = \strtoupper($direction ?? 'ASC');
            if (!\in_array($direction, ['ASC', 'DESC'], true)) {
                $direction = 'ASC';
            }
            $qb->orderBy('v.' . $sortField, $direction);
        } else {
            $qb->orderBy('v.immatriculation', 'ASC');
        }

        return $qb;
    }

    /**
     * @return Vehicule[]
     */
    public function searchAndSort(?string $term, ?string $sortField, ?string $direction): array
    {
        return $this->getSearchAndSortQueryBuilder($term, $sortField, $direction)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, array{type: string|null, total: int}>
     */
    public function getStatsByType(): array
    {
        return $this->createQueryBuilder('v')
            ->select('v.typeVehicule AS type', 'COUNT(v.id) AS total')
            ->groupBy('v.typeVehicule')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }
}
