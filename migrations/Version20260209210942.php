<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209210942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notifications (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, type VARCHAR(50) NOT NULL, date_creation DATETIME NOT NULL, lu TINYINT NOT NULL, ID_Utilisateur INT NOT NULL, ID_Colis INT DEFAULT NULL, INDEX IDX_6000B0D3FD8812BE (ID_Utilisateur), INDEX IDX_6000B0D35E84EA07 (ID_Colis), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3FD8812BE FOREIGN KEY (ID_Utilisateur) REFERENCES utilisateurs (ID_Utilisateur)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D35E84EA07 FOREIGN KEY (ID_Colis) REFERENCES colis (ID_Colis)');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY `commentaires_ibfk_1`');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY `commentaires_ibfk_2`');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY `devis_ibfk_1`');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY `devis_ibfk_2`');
        $this->addSql('ALTER TABLE factures DROP FOREIGN KEY `factures_ibfk_1`');
        $this->addSql('ALTER TABLE publications DROP FOREIGN KEY `publications_ibfk_1`');
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY `reclamations_ibfk_1`');
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY `reclamations_ibfk_2`');
        $this->addSql('ALTER TABLE recompenses DROP FOREIGN KEY `recompenses_ibfk_1`');
        $this->addSql('ALTER TABLE recompenses DROP FOREIGN KEY `recompenses_ibfk_2`');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY `reponses_ibfk_1`');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY `reponses_ibfk_2`');
        $this->addSql('ALTER TABLE vehicules DROP FOREIGN KEY `vehicules_ibfk_1`');
        $this->addSql('ALTER TABLE vehicules DROP FOREIGN KEY `vehicules_ibfk_2`');
        $this->addSql('DROP TABLE commentaires');
        $this->addSql('DROP TABLE devis');
        $this->addSql('DROP TABLE factures');
        $this->addSql('DROP TABLE publications');
        $this->addSql('DROP TABLE reclamations');
        $this->addSql('DROP TABLE recompenses');
        $this->addSql('DROP TABLE reponses');
        $this->addSql('DROP TABLE vehicules');
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY `colis_ibfk_1`');
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY `colis_ibfk_2`');
        $this->addSql('DROP INDEX idx_colis_statut ON colis');
        $this->addSql('ALTER TABLE colis CHANGE description description LONGTEXT DEFAULT NULL, CHANGE articles articles LONGTEXT DEFAULT NULL, CHANGE adresseDestination adresseDestination LONGTEXT NOT NULL, CHANGE dateCreation dateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP, CHANGE dateExpedition dateExpedition TIMESTAMP NULL DEFAULT NULL, CHANGE poids poids DOUBLE PRECISION DEFAULT NULL, CHANGE dimensions dimensions VARCHAR(100) DEFAULT NULL, CHANGE statut statut VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT FK_470BDFF9EA3CDC84 FOREIGN KEY (ID_Expediteur) REFERENCES utilisateurs (ID_Utilisateur)');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT FK_470BDFF980F8175E FOREIGN KEY (ID_Destinataire) REFERENCES utilisateurs (ID_Utilisateur)');
        $this->addSql('ALTER TABLE colis DROP INDEX idx_colis_expediteur');
        $this->addSql('ALTER TABLE colis ADD INDEX IDX_470BDFF9EA3CDC84 (ID_Expediteur)');
        $this->addSql('ALTER TABLE colis DROP INDEX idx_colis_destinataire');
        $this->addSql('ALTER TABLE colis ADD INDEX IDX_470BDFF980F8175E (ID_Destinataire)');
        $this->addSql('ALTER TABLE livraisons DROP FOREIGN KEY `livraisons_ibfk_1`');
        $this->addSql('ALTER TABLE livraisons DROP FOREIGN KEY `livraisons_ibfk_2`');
        $this->addSql('DROP INDEX idx_livraison_statut ON livraisons');
        $this->addSql('ALTER TABLE livraisons CHANGE statut statut VARCHAR(50) DEFAULT NULL, CHANGE dateDebut dateDebut TIMESTAMP DEFAULT CURRENT_TIMESTAMP, CHANGE dateFin dateFin TIMESTAMP NULL DEFAULT NULL, CHANGE distanceKm distanceKm DOUBLE PRECISION DEFAULT NULL, CHANGE payment payment DOUBLE PRECISION DEFAULT NULL, CHANGE total total DOUBLE PRECISION DEFAULT NULL, CHANGE methodePaiement methodePaiement VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE livraisons ADD CONSTRAINT FK_96A0CE615E84EA07 FOREIGN KEY (ID_Colis) REFERENCES colis (ID_Colis)');
        $this->addSql('ALTER TABLE livraisons ADD CONSTRAINT FK_96A0CE6137F65FF2 FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (ID_Utilisateur)');
        $this->addSql('ALTER TABLE livraisons DROP INDEX id_colis');
        $this->addSql('ALTER TABLE livraisons ADD INDEX IDX_96A0CE615E84EA07 (ID_Colis)');
        $this->addSql('ALTER TABLE livraisons DROP INDEX idx_livraison_livreur');
        $this->addSql('ALTER TABLE livraisons ADD INDEX IDX_96A0CE6137F65FF2 (ID_Livreur)');
        $this->addSql('DROP INDEX idx_utilisateur_role ON utilisateurs');
        $this->addSql('ALTER TABLE utilisateurs CHANGE telephone telephone VARCHAR(20) DEFAULT NULL, CHANGE role role VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaires (ID_Commentaire INT AUTO_INCREMENT NOT NULL, contenu TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, dateCreation DATETIME DEFAULT \'current_timestamp()\' NOT NULL, ID_Publication INT NOT NULL, ID_Auteur INT NOT NULL, INDEX ID_Auteur (ID_Auteur), INDEX ID_Publication (ID_Publication), PRIMARY KEY (ID_Commentaire)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE devis (ID_Devis INT AUTO_INCREMENT NOT NULL, montantEstime FLOAT NOT NULL, dateCreation DATETIME DEFAULT \'current_timestamp()\' NOT NULL, validite INT DEFAULT 30, ID_Entreprise INT NOT NULL, ID_Livraison INT DEFAULT NULL, INDEX ID_Entreprise (ID_Entreprise), INDEX ID_Livraison (ID_Livraison), PRIMARY KEY (ID_Devis)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE factures (ID_Facture INT AUTO_INCREMENT NOT NULL, numero VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, dateEmission DATETIME DEFAULT \'current_timestamp()\' NOT NULL, montantHT FLOAT NOT NULL, montantTTC FLOAT NOT NULL, tva FLOAT DEFAULT \'0\', statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'\'\'emise\'\'\' COLLATE `utf8mb4_general_ci`, ID_Livraison INT NOT NULL, INDEX idx_facture_numero (numero), UNIQUE INDEX numero (numero), INDEX ID_Livraison (ID_Livraison), PRIMARY KEY (ID_Facture)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE publications (ID_Publication INT AUTO_INCREMENT NOT NULL, titre VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, contenu TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, datePublication DATETIME DEFAULT \'current_timestamp()\' NOT NULL, statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'\'\'active\'\'\' COLLATE `utf8mb4_general_ci`, ID_Auteur INT NOT NULL, INDEX ID_Auteur (ID_Auteur), INDEX idx_publication_date (datePublication), PRIMARY KEY (ID_Publication)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reclamations (ID_Reclamation INT AUTO_INCREMENT NOT NULL, sujet VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, dateCreation DATETIME DEFAULT \'current_timestamp()\' NOT NULL, statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'\'\'ouverte\'\'\' COLLATE `utf8mb4_general_ci`, priorite VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'\'\'normale\'\'\' COLLATE `utf8mb4_general_ci`, ID_Livreur INT NOT NULL, ID_Administrateur INT DEFAULT NULL, INDEX ID_Livreur (ID_Livreur), INDEX ID_Administrateur (ID_Administrateur), INDEX idx_reclamation_statut (statut), PRIMARY KEY (ID_Reclamation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE recompenses (ID_Recompense INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, valeur FLOAT DEFAULT \'NULL\', dateObtention DATETIME DEFAULT \'current_timestamp()\' NOT NULL, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, seuil INT DEFAULT NULL, ID_Livreur INT NOT NULL, ID_Facture INT DEFAULT NULL, INDEX ID_Livreur (ID_Livreur), INDEX ID_Facture (ID_Facture), PRIMARY KEY (ID_Recompense)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reponses (ID_Reponse INT AUTO_INCREMENT NOT NULL, contenu TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, dateReponse DATETIME DEFAULT \'current_timestamp()\' NOT NULL, ID_Reclamation INT NOT NULL, ID_Administrateur INT NOT NULL, INDEX ID_Reclamation (ID_Reclamation), INDEX ID_Administrateur (ID_Administrateur), PRIMARY KEY (ID_Reponse)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE vehicules (ID_Vehicule INT AUTO_INCREMENT NOT NULL, marque VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, modele VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, immatriculation VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, type VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, capacite FLOAT DEFAULT \'NULL\', statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'\'\'disponible\'\'\' COLLATE `utf8mb4_general_ci`, ID_Livreur INT DEFAULT NULL, ID_Administrateur INT DEFAULT NULL, INDEX idx_vehicule_statut (statut), UNIQUE INDEX immatriculation (immatriculation), INDEX ID_Livreur (ID_Livreur), INDEX ID_Administrateur (ID_Administrateur), PRIMARY KEY (ID_Vehicule)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT `commentaires_ibfk_1` FOREIGN KEY (ID_Publication) REFERENCES publications (ID_Publication) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT `commentaires_ibfk_2` FOREIGN KEY (ID_Auteur) REFERENCES utilisateurs (ID_Utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT `devis_ibfk_1` FOREIGN KEY (ID_Entreprise) REFERENCES utilisateurs (ID_Utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT `devis_ibfk_2` FOREIGN KEY (ID_Livraison) REFERENCES livraisons (ID_Livraison) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE factures ADD CONSTRAINT `factures_ibfk_1` FOREIGN KEY (ID_Livraison) REFERENCES livraisons (ID_Livraison) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE publications ADD CONSTRAINT `publications_ibfk_1` FOREIGN KEY (ID_Auteur) REFERENCES utilisateurs (ID_Utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT `reclamations_ibfk_1` FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (ID_Utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT `reclamations_ibfk_2` FOREIGN KEY (ID_Administrateur) REFERENCES utilisateurs (ID_Utilisateur) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE recompenses ADD CONSTRAINT `recompenses_ibfk_1` FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (ID_Utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recompenses ADD CONSTRAINT `recompenses_ibfk_2` FOREIGN KEY (ID_Facture) REFERENCES factures (ID_Facture) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT `reponses_ibfk_1` FOREIGN KEY (ID_Reclamation) REFERENCES reclamations (ID_Reclamation) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT `reponses_ibfk_2` FOREIGN KEY (ID_Administrateur) REFERENCES utilisateurs (ID_Utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vehicules ADD CONSTRAINT `vehicules_ibfk_1` FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (ID_Utilisateur) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE vehicules ADD CONSTRAINT `vehicules_ibfk_2` FOREIGN KEY (ID_Administrateur) REFERENCES utilisateurs (ID_Utilisateur) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3FD8812BE');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D35E84EA07');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY FK_470BDFF9EA3CDC84');
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY FK_470BDFF980F8175E');
        $this->addSql('ALTER TABLE colis CHANGE description description TEXT DEFAULT NULL, CHANGE articles articles TEXT DEFAULT NULL, CHANGE adresseDestination adresseDestination TEXT NOT NULL, CHANGE dateCreation dateCreation DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE dateExpedition dateExpedition DATETIME DEFAULT \'NULL\', CHANGE poids poids FLOAT DEFAULT \'NULL\', CHANGE dimensions dimensions VARCHAR(100) DEFAULT \'NULL\', CHANGE statut statut VARCHAR(50) DEFAULT \'\'\'en_attente\'\'\'');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT `colis_ibfk_1` FOREIGN KEY (ID_Expediteur) REFERENCES utilisateurs (ID_Utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT `colis_ibfk_2` FOREIGN KEY (ID_Destinataire) REFERENCES utilisateurs (ID_Utilisateur) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_colis_statut ON colis (statut)');
        $this->addSql('ALTER TABLE colis RENAME INDEX idx_470bdff980f8175e TO idx_colis_destinataire');
        $this->addSql('ALTER TABLE colis RENAME INDEX idx_470bdff9ea3cdc84 TO idx_colis_expediteur');
        $this->addSql('ALTER TABLE livraisons DROP FOREIGN KEY FK_96A0CE615E84EA07');
        $this->addSql('ALTER TABLE livraisons DROP FOREIGN KEY FK_96A0CE6137F65FF2');
        $this->addSql('ALTER TABLE livraisons CHANGE statut statut VARCHAR(50) DEFAULT \'\'\'en_cours\'\'\', CHANGE dateDebut dateDebut DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE dateFin dateFin DATETIME DEFAULT \'NULL\', CHANGE distanceKm distanceKm FLOAT DEFAULT \'NULL\', CHANGE payment payment FLOAT DEFAULT \'NULL\', CHANGE total total FLOAT DEFAULT \'NULL\', CHANGE methodePaiement methodePaiement VARCHAR(50) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE livraisons ADD CONSTRAINT `livraisons_ibfk_1` FOREIGN KEY (ID_Colis) REFERENCES colis (ID_Colis) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE livraisons ADD CONSTRAINT `livraisons_ibfk_2` FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (ID_Utilisateur) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_livraison_statut ON livraisons (statut)');
        $this->addSql('ALTER TABLE livraisons RENAME INDEX idx_96a0ce6137f65ff2 TO idx_livraison_livreur');
        $this->addSql('ALTER TABLE livraisons RENAME INDEX idx_96a0ce615e84ea07 TO ID_Colis');
        $this->addSql('ALTER TABLE utilisateurs CHANGE telephone telephone VARCHAR(20) DEFAULT \'NULL\', CHANGE role role VARCHAR(255) DEFAULT \'\'\'Client\'\'\' NOT NULL');
        $this->addSql('CREATE INDEX idx_utilisateur_role ON utilisateurs (role)');
    }
}
