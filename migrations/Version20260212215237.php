<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212215237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE colis CHANGE dateCreation dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE factures ADD ID_Livraison INT NOT NULL');
        $this->addSql('ALTER TABLE factures ADD CONSTRAINT FK_647590B3A65F5B4 FOREIGN KEY (ID_Livraison) REFERENCES livraisons (ID_Livraison)');
        $this->addSql('CREATE INDEX IDX_647590B3A65F5B4 ON factures (ID_Livraison)');
        $this->addSql('ALTER TABLE livraisons CHANGE dateDebut dateDebut TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE colis CHANGE dateCreation dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE factures DROP FOREIGN KEY FK_647590B3A65F5B4');
        $this->addSql('DROP INDEX IDX_647590B3A65F5B4 ON factures');
        $this->addSql('ALTER TABLE factures DROP ID_Livraison');
        $this->addSql('ALTER TABLE livraisons CHANGE dateDebut dateDebut DATETIME DEFAULT CURRENT_TIMESTAMP');
    }
}
