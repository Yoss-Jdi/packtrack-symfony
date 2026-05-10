<?php

namespace App\Repository;

use App\Entity\Technician;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Technician>
 */
class TechnicianRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Technician::class);
    }

    /**
     * @return Technician[]
     */
    public function searchAndSort(?string $term, ?string $sortField, ?string $direction): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($term) {
            $qb
                ->andWhere('t.nom LIKE :term OR t.prenom LIKE :term OR t.email LIKE :term OR t.specialite LIKE :term OR t.telephone LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        // ⚠️ CHANGEMENT : 'statut' supprimé de la liste des champs de tri
        $allowedSorts = ['nom', 'prenom', 'email', 'specialite', 'telephone'];
        if ($sortField && \in_array($sortField, $allowedSorts, true)) {
            $direction = \strtoupper($direction ?? 'ASC');
            if (!\in_array($direction, ['ASC', 'DESC'], true)) {
                $direction = 'ASC';
            }
            $qb->orderBy('t.' . $sortField, $direction);
        } else {
            $qb->orderBy('t.nom', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }
}