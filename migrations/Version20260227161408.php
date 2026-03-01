<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227161408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
{
    $this->addSql('ALTER TABLE utilisateurs CHANGE telephone telephone VARCHAR(20) DEFAULT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL');
    $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
}

public function down(Schema $schema): void
{
    $this->addSql('ALTER TABLE utilisateurs CHANGE telephone telephone VARCHAR(20) NOT NULL, CHANGE photo photo VARCHAR(255) NOT NULL');
    $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME NOT NULL');
}
}
