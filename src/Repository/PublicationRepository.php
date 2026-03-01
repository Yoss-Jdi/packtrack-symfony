<?php

namespace App\Repository;

use App\Entity\Publication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Publication>
 */
class PublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publication::class);
    }

    /**
     * @return Publication[]
     */
    public function findLatestActive(int $limit = 20): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.statut = :active')
            ->setParameter('active', 'active')
            ->orderBy('p.datePublication', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
