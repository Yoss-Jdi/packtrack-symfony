<?php
namespace App\Tests\Service;

use App\Entity\PublicationReaction;
use App\Service\PublicationReactionManager;
use PHPUnit\Framework\TestCase;

class PublicationReactionManagerTest extends TestCase
{
    private PublicationReactionManager $manager;

    protected function setUp(): void
    {
        $this->manager = new PublicationReactionManager();
    }

    // ✅ Test 1 : Réaction LIKE valide
    public function testReactionLikeValide(): void
    {
        $reaction = new PublicationReaction();
        $reaction->setReaction(PublicationReaction::LIKE);

        $this->assertTrue($this->manager->validate($reaction));
    }

    // ✅ Test 2 : Réaction DISLIKE valide
    public function testReactionDislikeValide(): void
    {
        $reaction = new PublicationReaction();
        $reaction->setReaction(PublicationReaction::DISLIKE);

        $this->assertTrue($this->manager->validate($reaction));
    }

    // ❌ Test 3 : Réaction invalide
    public function testReactionInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("La réaction doit être LIKE (1) ou DISLIKE (-1)");

        $reaction = new PublicationReaction();
        $reaction->setReaction(0);

        $this->manager->validate($reaction);
    }

    // ✅ Test 4 : isLike retourne true
    public function testIsLikeTrue(): void
    {
        $reaction = new PublicationReaction();
        $reaction->setReaction(PublicationReaction::LIKE);

        $this->assertTrue($this->manager->isLike($reaction));
    }

    // ✅ Test 5 : isDislike retourne true
    public function testIsDislikeTrue(): void
    {
        $reaction = new PublicationReaction();
        $reaction->setReaction(PublicationReaction::DISLIKE);

        $this->assertTrue($this->manager->isDislike($reaction));
    }
}