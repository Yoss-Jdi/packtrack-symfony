<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222052719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE colis CHANGE dateCreation dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
       // $this->addSql('ALTER TABLE factures ADD pdfUrl VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE livraisons CHANGE dateDebut dateDebut TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        //$this->addSql('ALTER TABLE recompenses DROP FOREIGN KEY `recompenses_ibfk_1`');
        $this->addSql('ALTER TABLE recompenses CHANGE valeur valeur DOUBLE PRECISION DEFAULT NULL, CHANGE dateObtention dateObtention DATETIME DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE recompenses ADD CONSTRAINT FK_7A7BAB1837F65FF2 FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (ID_Utilisateur)');
       // $this->addSql('ALTER TABLE recompenses RENAME INDEX id_livreur TO IDX_7A7BAB1837F65FF2');
        $this->addSql('ALTER TABLE recompenses RENAME INDEX id_facture TO IDX_7A7BAB18220A758F');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE colis CHANGE dateCreation dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE factures DROP pdfUrl');
        $this->addSql('ALTER TABLE livraisons CHANGE dateDebut dateDebut DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE recompenses DROP FOREIGN KEY FK_7A7BAB1837F65FF2');
        $this->addSql('ALTER TABLE recompenses CHANGE valeur valeur FLOAT DEFAULT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE dateObtention dateObtention DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE recompenses ADD CONSTRAINT `recompenses_ibfk_1` FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (ID_Utilisateur) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recompenses RENAME INDEX idx_7a7bab18220a758f TO ID_Facture');
        $this->addSql('ALTER TABLE recompenses RENAME INDEX idx_7a7bab1837f65ff2 TO ID_Livreur');
    }
}
