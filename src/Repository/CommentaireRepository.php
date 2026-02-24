<?php

namespace App\Repository;

use App\Entity\Commentaire;
use App\Entity\Publication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commentaire>
 */
class CommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentaire::class);
    }

    /**
     * @return Commentaire[]
     */
    public function findForPublication(Publication $publication): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.publication = :p')
            ->setParameter('p', $publication)
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
