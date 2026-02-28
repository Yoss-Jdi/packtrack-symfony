<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260228154359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE password_reset_tokens (id BINARY(16) NOT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, used TINYINT NOT NULL, UNIQUE INDEX UNIQ_3967A2165F37A13B (token), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateurs (id_utilisateur INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, role VARCHAR(50) NOT NULL, photo VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_497B315EE7927C74 (email), PRIMARY KEY (id_utilisateur)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE password_reset_tokens');
        $this->addSql('DROP TABLE utilisateurs');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
