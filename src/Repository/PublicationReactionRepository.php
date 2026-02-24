<?php

namespace App\Repository;

use App\Entity\Publication;
use App\Entity\PublicationReaction;
use App\Entity\Utilisateurs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PublicationReaction>
 */
class PublicationReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicationReaction::class);
    }

    public function findOneForUser(Publication $publication, Utilisateurs $user): ?PublicationReaction
    {
        return $this->findOneBy([
            'publication' => $publication,
            'auteur' => $user,
        ]);
    }

    /**
     * @return array{likes:int, dislikes:int}
     */
    public function getCounts(Publication $publication): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select(
                'SUM(CASE WHEN r.reaction = 1 THEN 1 ELSE 0 END) AS likes',
                'SUM(CASE WHEN r.reaction = -1 THEN 1 ELSE 0 END) AS dislikes'
            )
            ->andWhere('r.publication = :p')
            ->setParameter('p', $publication);

        $row = $qb->getQuery()->getSingleResult();

        return [
            'likes' => (int) ($row['likes'] ?? 0),
            'dislikes' => (int) ($row['dislikes'] ?? 0),
        ];
    }
}
