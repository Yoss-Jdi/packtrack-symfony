<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add techniciens table and ID_Technicien column to vehicules for Vehicle-Technician CRUD.
 */
final class Version20260211130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add techniciens table and ID_Technicien to vehicules';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on "mysql".'
        );

        $this->addSql(<<<'SQL'
CREATE TABLE techniciens (
    ID_Technicien INT AUTO_INCREMENT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    specialite VARCHAR(100) DEFAULT NULL,
    telephone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(180) NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'disponible',
    PRIMARY KEY(ID_Technicien),
    UNIQUE INDEX UNIQ_technicien_email (email)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB
SQL);

        $this->addSql('ALTER TABLE vehicules ADD ID_Technicien INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicules ADD CONSTRAINT FK_vehicule_technicien FOREIGN KEY (ID_Technicien) REFERENCES techniciens (ID_Technicien) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_vehicule_technicien ON vehicules (ID_Technicien)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on "mysql".'
        );

        $this->addSql('ALTER TABLE vehicules DROP FOREIGN KEY FK_vehicule_technicien');
        $this->addSql('DROP INDEX IDX_vehicule_technicien ON vehicules');
        $this->addSql('ALTER TABLE vehicules DROP ID_Technicien');
        $this->addSql('DROP TABLE techniciens');
    }
}
