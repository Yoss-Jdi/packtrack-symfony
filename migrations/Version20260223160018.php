<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223160018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE colis (ID_Colis INT AUTO_INCREMENT NOT NULL, description LONGTEXT DEFAULT NULL, articles LONGTEXT DEFAULT NULL, adresseDestination LONGTEXT NOT NULL, adresseDepart LONGTEXT NOT NULL, dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP, dateExpedition TIMESTAMP NULL DEFAULT NULL, poids DOUBLE PRECISION DEFAULT NULL, dimensions VARCHAR(100) DEFAULT NULL, statut VARCHAR(50) DEFAULT NULL, qrCode LONGTEXT DEFAULT NULL, ID_Expediteur INT NOT NULL, ID_Destinataire INT NOT NULL, INDEX IDX_470BDFF9EA3CDC84 (ID_Expediteur), INDEX IDX_470BDFF980F8175E (ID_Destinataire), PRIMARY KEY (ID_Colis)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE factures (ID_Facture INT AUTO_INCREMENT NOT NULL, numero VARCHAR(50) NOT NULL, dateEmission DATETIME DEFAULT NULL, montantHT DOUBLE PRECISION NOT NULL, montantTTC DOUBLE PRECISION NOT NULL, tva DOUBLE PRECISION DEFAULT NULL, statut VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_647590BF55AE19E (numero), PRIMARY KEY (ID_Facture)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE livraisons (ID_Livraison INT AUTO_INCREMENT NOT NULL, statut VARCHAR(50) DEFAULT NULL, dateDebut TIMESTAMP DEFAULT CURRENT_TIMESTAMP, dateFin TIMESTAMP NULL DEFAULT NULL, distanceKm DOUBLE PRECISION DEFAULT NULL, payment DOUBLE PRECISION DEFAULT NULL, total DOUBLE PRECISION DEFAULT NULL, methodePaiement VARCHAR(50) DEFAULT NULL, dureeEstimeeMinutes DOUBLE PRECISION DEFAULT NULL, ID_Colis INT NOT NULL, ID_Livreur INT NOT NULL, INDEX IDX_96A0CE615E84EA07 (ID_Colis), INDEX IDX_96A0CE6137F65FF2 (ID_Livreur), PRIMARY KEY (ID_Livraison)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notifications (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, type VARCHAR(50) NOT NULL, date_creation DATETIME NOT NULL, lu TINYINT NOT NULL, ID_Utilisateur INT NOT NULL, ID_Colis INT DEFAULT NULL, INDEX IDX_6000B0D3FD8812BE (ID_Utilisateur), INDEX IDX_6000B0D35E84EA07 (ID_Colis), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE password_reset_tokens (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, used TINYINT NOT NULL, UNIQUE INDEX UNIQ_3967A2165F37A13B (token), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateurs (ID_Utilisateur INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, role VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_497B315EE7927C74 (email), PRIMARY KEY (ID_Utilisateur)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT FK_470BDFF9EA3CDC84 FOREIGN KEY (ID_Expediteur) REFERENCES utilisateurs (ID_Utilisateur)');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT FK_470BDFF980F8175E FOREIGN KEY (ID_Destinataire) REFERENCES utilisateurs (ID_Utilisateur)');
        $this->addSql('ALTER TABLE livraisons ADD CONSTRAINT FK_96A0CE615E84EA07 FOREIGN KEY (ID_Colis) REFERENCES colis (ID_Colis)');
        $this->addSql('ALTER TABLE livraisons ADD CONSTRAINT FK_96A0CE6137F65FF2 FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (ID_Utilisateur)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3FD8812BE FOREIGN KEY (ID_Utilisateur) REFERENCES utilisateurs (ID_Utilisateur)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D35E84EA07 FOREIGN KEY (ID_Colis) REFERENCES colis (ID_Colis)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY FK_470BDFF9EA3CDC84');
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY FK_470BDFF980F8175E');
        $this->addSql('ALTER TABLE livraisons DROP FOREIGN KEY FK_96A0CE615E84EA07');
        $this->addSql('ALTER TABLE livraisons DROP FOREIGN KEY FK_96A0CE6137F65FF2');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3FD8812BE');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D35E84EA07');
        $this->addSql('DROP TABLE colis');
        $this->addSql('DROP TABLE factures');
        $this->addSql('DROP TABLE livraisons');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE password_reset_tokens');
        $this->addSql('DROP TABLE utilisateurs');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
