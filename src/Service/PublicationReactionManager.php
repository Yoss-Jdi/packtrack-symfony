<?php
namespace App\Service;

use App\Entity\PublicationReaction;

class PublicationReactionManager
{
    private const REACTIONS_VALIDES = [
        PublicationReaction::LIKE,
        PublicationReaction::DISLIKE
    ];

    public function validate(PublicationReaction $reaction): bool
    {
        // Règle 1 : Réaction doit être LIKE (1) ou DISLIKE (-1)
        if (!in_array($reaction->getReaction(), self::REACTIONS_VALIDES, true)) {
            throw new \InvalidArgumentException("La réaction doit être LIKE (1) ou DISLIKE (-1)");
        }

        return true;
    }

    public function isLike(PublicationReaction $reaction): bool
    {
        return $reaction->getReaction() === PublicationReaction::LIKE;
    }

    public function isDislike(PublicationReaction $reaction): bool
    {
        return $reaction->getReaction() === PublicationReaction::DISLIKE;
    }
}