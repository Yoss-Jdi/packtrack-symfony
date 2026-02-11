<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208173902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY `colis_ibfk_1`');
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY `colis_ibfk_2`');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY `commentaires_ibfk_1`');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY `commentaires_ibfk_2`');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY `devis_ibfk_1`');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY `devis_ibfk_2`');
        $this->addSql('ALTER TABLE livraisons DROP FOREIGN KEY `livraisons_ibfk_1`');
        $this->addSql('ALTER TABLE livraisons DROP FOREIGN KEY `livraisons_ibfk_2`');
        $this->addSql('ALTER TABLE publications DROP FOREIGN KEY `publications_ibfk_1`');
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY `reclamations_ibfk_1`');
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY `reclamations_ibfk_2`');
        $this->addSql('ALTER TABLE recompenses DROP FOREIGN KEY `recompenses_ibfk_1`');
        $this->addSql('ALTER TABLE recompenses DROP FOREIGN KEY `recompenses_ibfk_2`');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY `reponses_ibfk_1`');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY `reponses_ibfk_2`');
        $this->addSql('ALTER TABLE vehicules DROP FOREIGN KEY `vehicules_ibfk_1`');
        $this->addSql('ALTER TABLE vehicules DROP FOREIGN KEY `vehicules_ibfk_2`');
        $this->addSql('DROP TABLE colis');
        $this->addSql('DROP TABLE commentaires');
        $this->addSql('DROP TABLE devis');
        $this->addSql('DROP TABLE livraisons');
        $this->addSql('DROP TABLE publications');
        $this->addSql('DROP TABLE reclamations');
        $this->addSql('DROP TABLE recompenses');
        $this->addSql('DROP TABLE reponses');
        $this->addSql('DROP TABLE utilisateurs');
        $this->addSql('DROP TABLE vehicules');
        $this->addSql('DROP INDEX idx_facture_numero ON factures');
        $this->addSql('ALTER TABLE factures ADD date_emission VARCHAR(255) DEFAULT NULL, ADD montant_ht DOUBLE PRECISION NOT NULL, ADD montant_ttc DOUBLE PRECISION NOT NULL, DROP dateEmission, DROP montantHT, DROP montantTTC, CHANGE tva tva DOUBLE PRECISION DEFAULT NULL, CHANGE statut statut VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE factures RENAME INDEX numero TO UNIQ_647590BF55AE19E');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE colis (ID_Colis INT AUTO_INCREMENT NOT NULL, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, articles TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, adresseDestination TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP, dateExpedition DATETIME DEFAULT NULL, poids FLOAT DEFAULT NULL, dimensions VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'en_attente\' COLLATE `utf8mb4_general_ci`, ID_Expediteur INT NOT NULL, ID_Destinataire INT NOT NULL, INDEX idx_colis_destinataire (ID_Destinataire), INDEX idx_colis_expediteur (ID_Expediteur), INDEX idx_colis_statut (statut), PRIMARY KEY (ID_Colis)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE commentaires (ID_Commentaire INT AUTO_INCREMENT NOT NULL, contenu TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP, ID_Publication INT NOT NULL, ID_Auteur INT NOT NULL, INDEX ID_Auteur (ID_Auteur), INDEX ID_Publication (ID_Publication), PRIMARY KEY (ID_Commentaire)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE devis (ID_Devis INT AUTO_INCREMENT NOT NULL, montantEstime FLOAT NOT NULL, dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP, validite INT DEFAULT 30, ID_Entreprise INT NOT NULL, ID_Livraison INT DEFAULT NULL, INDEX ID_Entreprise (ID_Entreprise), INDEX ID_Livraison (ID_Livraison), PRIMARY KEY (ID_Devis)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE livraisons (ID_Livraison INT AUTO_INCREMENT NOT NULL, statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'en_cours\' COLLATE `utf8mb4_general_ci`, dateDebut DATETIME DEFAULT CURRENT_TIMESTAMP, dateFin DATETIME DEFAULT NULL, distanceKm FLOAT DEFAULT NULL, payment FLOAT DEFAULT NULL, total FLOAT DEFAULT NULL, methodePaiement VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, ID_Colis INT NOT NULL, ID_Livreur INT NOT NULL, INDEX ID_Colis (ID_Colis), INDEX idx_livraison_livreur (ID_Livreur), INDEX idx_livraison_statut (statut), PRIMARY KEY (ID_Livraison)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE publications (ID_Publication INT AUTO_INCREMENT NOT NULL, titre VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, contenu TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, datePublication DATETIME DEFAULT CURRENT_TIMESTAMP, statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'active\' COLLATE `utf8mb4_general_ci`, ID_Auteur INT NOT NULL, INDEX ID_Auteur (ID_Auteur), INDEX idx_publication_date (datePublication), PRIMARY KEY (ID_Publication)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reclamations (ID_Reclamation INT AUTO_INCREMENT NOT NULL, sujet VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP, statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'ouverte\' COLLATE `utf8mb4_general_ci`, priorite VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'normale\' COLLATE `utf8mb4_general_ci`, ID_Livreur INT NOT NULL, ID_Administrateur INT DEFAULT NULL, INDEX ID_Administrateur (ID_Administrateur), INDEX ID_Livreur (ID_Livreur), INDEX idx_reclamation_statut (statut), PRIMARY KEY (ID_Reclamation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE recompenses (ID_Recompense INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, valeur FLOAT DEFAULT NULL, dateObtention DATETIME DEFAULT CURRENT_TIMESTAMP, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, seuil INT DEFAULT NULL, ID_Livreur INT NOT NULL, ID_Facture INT DEFAULT NULL, INDEX ID_Facture (ID_Facture), INDEX ID_Livreur (ID_Livreur), PRIMARY KEY (ID_Recompense)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reponses (ID_Reponse INT AUTO_INCREMENT NOT NULL, contenu TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, dateReponse DATETIME DEFAULT CURRENT_TIMESTAMP, ID_Reclamation INT NOT NULL, ID_Administrateur INT NOT NULL, INDEX ID_Administrateur (ID_Administrateur), INDEX ID_Reclamation (ID_Reclamation), PRIMARY KEY (ID_Reponse)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE utilisateurs (ID_Utilisateur INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, prenom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, telephone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, mot_de_passe VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, role VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'Client\' NOT NULL COLLATE `utf8mb4_general_ci`, INDEX idx_utilisateur_role (role), UNIQUE INDEX UNIQ_497B315EE7927C74 (email), PRIMARY KEY (ID_Utilisateur)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE vehicules (ID_Vehicule INT AUTO_INCREMENT NOT NULL, marque VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, modele VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, immatriculation VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, type VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, capacite FLOAT DEFAULT NULL, statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'disponible\' COLLATE `utf8mb4_general_ci`, ID_Livreur INT DEFAULT NULL, ID_Administrateur INT DEFAULT NULL, INDEX ID_Administrateur (ID_Administrateur), INDEX ID_Livreur (ID_Livreur), INDEX idx_vehicule_statut (statut), UNIQUE INDEX immatriculation (immatriculation), PRIMARY KEY (ID_Vehicule)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT `colis_ibfk_1` FOREIGN KEY (ID_Expediteur) REFERENCES utilisateurs (ID_Utilisateur) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT `colis_ibfk_2` FOREIGN KEY (ID_Destinataire) REFERENCES utilisateurs (ID_Utilisateur) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT `commentaires_ibfk_1` FOREIGN KEY (ID_Publication) REFERENCES publications (ID_Publication) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT `commentaires_ibfk_2` FOREIGN KEY (ID_Auteur) REFERENCES utilisateurs (ID_Utilisateur) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT `devis_ibfk_1` FOREIGN KEY (ID_Entreprise) REFERENCES utilisateurs (ID_Utilisateur) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT `devis_ibfk_2` FOREIGN KEY (ID_Livraison) REFERENCES livraisons (ID_Livraison) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE livraisons ADD CONSTRAINT `livraisons_ibfk_1` FOREIGN KEY (ID_Colis) REFERENCES colis (ID_Colis) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE livraisons ADD CONSTRAINT `livraisons_ibfk_2` FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (ID_Utilisateur) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE publications ADD CONSTRAINT `publications_ibfk_1` FOREIGN KEY (ID_Auteur) REFERENCES utilisateurs (ID_Utilisateur) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT `reclamations_ibfk_1` FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (ID_Utilisateur) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT `reclamations_ibfk_2` FOREIGN KEY (ID_Administrateur) REFERENCES utilisateurs (ID_Utilisateur) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE recompenses ADD CONSTRAINT `recompenses_ibfk_1` FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (ID_Utilisateur) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recompenses ADD CONSTRAINT `recompenses_ibfk_2` FOREIGN KEY (ID_Facture) REFERENCES factures (ID_Facture) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT `reponses_ibfk_1` FOREIGN KEY (ID_Reclamation) REFERENCES reclamations (ID_Reclamation) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT `reponses_ibfk_2` FOREIGN KEY (ID_Administrateur) REFERENCES utilisateurs (ID_Utilisateur) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vehicules ADD CONSTRAINT `vehicules_ibfk_1` FOREIGN KEY (ID_Livreur) REFERENCES utilisateurs (ID_Utilisateur) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE vehicules ADD CONSTRAINT `vehicules_ibfk_2` FOREIGN KEY (ID_Administrateur) REFERENCES utilisateurs (ID_Utilisateur) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE factures ADD dateEmission DATETIME DEFAULT CURRENT_TIMESTAMP, ADD montantHT FLOAT NOT NULL, ADD montantTTC FLOAT NOT NULL, DROP date_emission, DROP montant_ht, DROP montant_ttc, CHANGE tva tva FLOAT DEFAULT \'0\', CHANGE statut statut VARCHAR(50) DEFAULT \'emise\'');
        $this->addSql('CREATE INDEX idx_facture_numero ON factures (numero)');
        $this->addSql('ALTER TABLE factures RENAME INDEX uniq_647590bf55ae19e TO numero');
    }
}
