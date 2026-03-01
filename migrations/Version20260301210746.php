<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301210746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE colis (ID_Colis INT AUTO_INCREMENT NOT NULL, description LONGTEXT DEFAULT NULL, articles LONGTEXT DEFAULT NULL, adresseDestination LONGTEXT NOT NULL, adresseDepart LONGTEXT NOT NULL, dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP, dateExpedition TIMESTAMP NULL DEFAULT NULL, poids DOUBLE PRECISION DEFAULT NULL, dimensions VARCHAR(100) DEFAULT NULL, statut VARCHAR(50) DEFAULT NULL, qrCode LONGTEXT DEFAULT NULL, ID_Expediteur INT NOT NULL, ID_Destinataire INT NOT NULL, INDEX IDX_470BDFF9EA3CDC84 (ID_Expediteur), INDEX IDX_470BDFF980F8175E (ID_Destinataire), PRIMARY KEY (ID_Colis)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE commentaires (ID_Commentaire INT AUTO_INCREMENT NOT NULL, contenu LONGTEXT NOT NULL, dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ID_Publication INT NOT NULL, ID_Auteur INT NOT NULL, INDEX IDX_D9BEC0C44FA81674 (ID_Publication), INDEX IDX_D9BEC0C45F46E16E (ID_Auteur), PRIMARY KEY (ID_Commentaire)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE factures (ID_Facture INT AUTO_INCREMENT NOT NULL, numero VARCHAR(50) NOT NULL, dateEmission DATETIME DEFAULT NULL, montantHT DOUBLE PRECISION NOT NULL, montantTTC DOUBLE PRECISION NOT NULL, tva DOUBLE PRECISION DEFAULT NULL, statut VARCHAR(50) DEFAULT NULL, pdfUrl VARCHAR(500) DEFAULT NULL, ID_Livraison INT NOT NULL, UNIQUE INDEX UNIQ_647590BF55AE19E (numero), INDEX IDX_647590B3A65F5B4 (ID_Livraison), PRIMARY KEY (ID_Facture)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE livraisons (ID_Livraison INT AUTO_INCREMENT NOT NULL, statut VARCHAR(50) DEFAULT NULL, dateDebut TIMESTAMP DEFAULT CURRENT_TIMESTAMP, dateFin TIMESTAMP NULL DEFAULT NULL, distanceKm DOUBLE PRECISION DEFAULT NULL, payment DOUBLE PRECISION DEFAULT NULL, total DOUBLE PRECISION DEFAULT NULL, methodePaiement VARCHAR(50) DEFAULT NULL, dureeEstimeeMinutes DOUBLE PRECISION DEFAULT NULL, ID_Colis INT NOT NULL, ID_Livreur INT NOT NULL, INDEX IDX_96A0CE615E84EA07 (ID_Colis), INDEX IDX_96A0CE6137F65FF2 (ID_Livreur), PRIMARY KEY (ID_Livraison)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notifications (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, type VARCHAR(50) NOT NULL, date_creation DATETIME NOT NULL, lu TINYINT NOT NULL, ID_Utilisateur INT NOT NULL, ID_Colis INT DEFAULT NULL, INDEX IDX_6000B0D3FD8812BE (ID_Utilisateur), INDEX IDX_6000B0D35E84EA07 (ID_Colis), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE password_reset_tokens (id BINARY(16) NOT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, used TINYINT NOT NULL, UNIQUE INDEX UNIQ_3967A2165F37A13B (token), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE publication_reactions (ID_Reaction INT AUTO_INCREMENT NOT NULL, reaction SMALLINT NOT NULL, dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ID_Publication INT NOT NULL, ID_Auteur INT NOT NULL, INDEX IDX_243079244FA81674 (ID_Publication), INDEX IDX_243079245F46E16E (ID_Auteur), UNIQUE INDEX uniq_publication_auteur (ID_Publication, ID_Auteur), PRIMARY KEY (ID_Reaction)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE publications (ID_Publication INT AUTO_INCREMENT NOT NULL, titre VARCHAR(200) NOT NULL, contenu LONGTEXT DEFAULT NULL, datePublication DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, statut VARCHAR(50) DEFAULT \'active\' NOT NULL, ID_Auteur INT NOT NULL, INDEX IDX_32783AF45F46E16E (ID_Auteur), INDEX idx_publication_date (datePublication), PRIMARY KEY (ID_Publication)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE recompenses (ID_Recompense INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) DEFAULT NULL, valeur DOUBLE PRECISION DEFAULT NULL, description LONGTEXT DEFAULT NULL, seuil INT DEFAULT NULL, dateObtention DATETIME DEFAULT NULL, ID_Livreur INT NOT NULL, ID_Facture INT DEFAULT NULL, INDEX IDX_7A7BAB1837F65FF2 (ID_Livreur), INDEX IDX_7A7BAB18220A758F (ID_Facture), PRIMARY KEY (ID_Recompense)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE techniciens (ID_Technicien INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, specialite VARCHAR(100) DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, email VARCHAR(180) NOT NULL, statut VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_64F2EA9CE7927C74 (email), PRIMARY KEY (ID_Technicien)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateurs (id_utilisateur INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, role VARCHAR(50) NOT NULL, photo VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_497B315EE7927C74 (email), PRIMARY KEY (id_utilisateur)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vehicules (ID_Vehicule INT AUTO_INCREMENT NOT NULL, marque VARCHAR(50) DEFAULT NULL, modele VARCHAR(50) DEFAULT NULL, immatriculation VARCHAR(20) NOT NULL, type VARCHAR(50) DEFAULT NULL, capacite DOUBLE PRECISION DEFAULT NULL, statut VARCHAR(50) NOT NULL, problem_description LONGTEXT DEFAULT NULL, ID_Technicien INT DEFAULT NULL, UNIQUE INDEX UNIQ_78218C2DBE73422E (immatriculation), INDEX IDX_78218C2DD7B03F46 (ID_Technicien), PRIMARY KEY (ID_Vehicule)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT FK_470BDFF9EA3CDC84 FOREIGN KEY (ID_Expediteur) REFERENCES utilisateurs (id_utilisateur)');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT FK_470BDFF980F8175E FOREIGN KEY (ID_Destinataire) REFERENCES utilisateurs (id_utilisateur)');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C44FA81674 FOREIGN KEY (ID_Publication) REFERENCES publications (ID_Publication) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C45F46E16E FOREIGN KEY (ID_Auteur) REFERENCES utilisateurs (id_utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE factures ADD CONSTRAINT FK_647590B3A65F5B4 FOREIGN KEY (ID_Livraison) REFERENCES livraisons (ID_Livraison)');
        $this->addSql('ALTER TABLE livraisons ADD CONSTRAINT FK_96A0CE615E84EA07 FOREIGN KEY (ID_Colis) REFERENCES colis (ID_Colis)');
        $this->addSql('ALTER TABLE livraisons ADD CONSTRAINT FK_96A0CE6137F65FF2 FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (id_utilisateur)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3FD8812BE FOREIGN KEY (ID_Utilisateur) REFERENCES utilisateurs (id_utilisateur)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D35E84EA07 FOREIGN KEY (ID_Colis) REFERENCES colis (ID_Colis)');
        $this->addSql('ALTER TABLE publication_reactions ADD CONSTRAINT FK_243079244FA81674 FOREIGN KEY (ID_Publication) REFERENCES publications (ID_Publication) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE publication_reactions ADD CONSTRAINT FK_243079245F46E16E FOREIGN KEY (ID_Auteur) REFERENCES utilisateurs (id_utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE publications ADD CONSTRAINT FK_32783AF45F46E16E FOREIGN KEY (ID_Auteur) REFERENCES utilisateurs (id_utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recompenses ADD CONSTRAINT FK_7A7BAB1837F65FF2 FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (id_utilisateur)');
        $this->addSql('ALTER TABLE recompenses ADD CONSTRAINT FK_7A7BAB18220A758F FOREIGN KEY (ID_Facture) REFERENCES factures (ID_Facture) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE vehicules ADD CONSTRAINT FK_78218C2DD7B03F46 FOREIGN KEY (ID_Technicien) REFERENCES techniciens (ID_Technicien) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY FK_470BDFF9EA3CDC84');
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY FK_470BDFF980F8175E');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C44FA81674');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C45F46E16E');
        $this->addSql('ALTER TABLE factures DROP FOREIGN KEY FK_647590B3A65F5B4');
        $this->addSql('ALTER TABLE livraisons DROP FOREIGN KEY FK_96A0CE615E84EA07');
        $this->addSql('ALTER TABLE livraisons DROP FOREIGN KEY FK_96A0CE6137F65FF2');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3FD8812BE');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D35E84EA07');
        $this->addSql('ALTER TABLE publication_reactions DROP FOREIGN KEY FK_243079244FA81674');
        $this->addSql('ALTER TABLE publication_reactions DROP FOREIGN KEY FK_243079245F46E16E');
        $this->addSql('ALTER TABLE publications DROP FOREIGN KEY FK_32783AF45F46E16E');
        $this->addSql('ALTER TABLE recompenses DROP FOREIGN KEY FK_7A7BAB1837F65FF2');
        $this->addSql('ALTER TABLE recompenses DROP FOREIGN KEY FK_7A7BAB18220A758F');
        $this->addSql('ALTER TABLE vehicules DROP FOREIGN KEY FK_78218C2DD7B03F46');
        $this->addSql('DROP TABLE colis');
        $this->addSql('DROP TABLE commentaires');
        $this->addSql('DROP TABLE factures');
        $this->addSql('DROP TABLE livraisons');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE password_reset_tokens');
        $this->addSql('DROP TABLE publication_reactions');
        $this->addSql('DROP TABLE publications');
        $this->addSql('DROP TABLE recompenses');
        $this->addSql('DROP TABLE techniciens');
        $this->addSql('DROP TABLE utilisateurs');
        $this->addSql('DROP TABLE vehicules');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
