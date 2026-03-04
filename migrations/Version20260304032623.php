<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304032623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create factures_maintenance table for vehicle maintenance invoices';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE factures_maintenance (
            ID_Facture_Maintenance INT AUTO_INCREMENT NOT NULL,
            vehicule_id INT NOT NULL,
            technicien_id INT DEFAULT NULL,
            numero VARCHAR(100) NOT NULL,
            date_emission DATETIME NOT NULL,
            montant_ht NUMERIC(10, 2) NOT NULL,
            montant_ttc NUMERIC(10, 2) NOT NULL,
            taux_tva NUMERIC(5, 2) NOT NULL,
            description_travaux LONGTEXT NOT NULL,
            fournisseur VARCHAR(255) DEFAULT NULL,
            piece_changees LONGTEXT DEFAULT NULL,
            UNIQUE INDEX UNIQ_C2468EBF55AE19E (numero),
            INDEX IDX_C2468EB4A4A3511 (vehicule_id),
            INDEX IDX_C2468EB13457256 (technicien_id),
            PRIMARY KEY(ID_Facture_Maintenance)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE factures_maintenance 
            ADD CONSTRAINT FK_C2468EB4A4A3511 
            FOREIGN KEY (vehicule_id) 
            REFERENCES vehicules (ID_Vehicule) 
            ON DELETE CASCADE');

        $this->addSql('ALTER TABLE factures_maintenance 
            ADD CONSTRAINT FK_C2468EB13457256 
            FOREIGN KEY (technicien_id) 
            REFERENCES techniciens (ID_Technicien) 
            ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE factures_maintenance DROP FOREIGN KEY FK_C2468EB4A4A3511');
        $this->addSql('ALTER TABLE factures_maintenance DROP FOREIGN KEY FK_C2468EB13457256');
        $this->addSql('DROP TABLE factures_maintenance');
    }
}
