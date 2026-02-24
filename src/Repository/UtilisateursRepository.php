<?php

namespace App\Repository;

use App\Entity\Utilisateurs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Utilisateurs>
 */
class UtilisateursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateurs::class);
    }

    /**
     * Rechercher des utilisateurs par nom, prénom ou email
     */
    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.Nom LIKE :term')
            ->orWhere('u.Prenom LIKE :term')
            ->orWhere('u.Email LIKE :term')
            ->setParameter('term', '%' . $searchTerm . '%')
            ->orderBy('u.Nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}