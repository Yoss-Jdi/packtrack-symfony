-- ==================== Création de la base de données ====================
CREATE DATABASE IF NOT EXISTS TrackPackDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE TrackPackDB;

-- ==================== Table Utilisateurs ====================
CREATE TABLE `utilisateurs` (
    `ID_Utilisateur` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(180) NOT NULL,
    `nom` VARCHAR(100) NOT NULL,
    `prenom` VARCHAR(100) NOT NULL,
    `telephone` VARCHAR(20) DEFAULT NULL,
    `mot_de_passe` VARCHAR(255) NOT NULL,
    `role` VARCHAR(255) NOT NULL DEFAULT 'Client',
    PRIMARY KEY (`ID_Utilisateur`),
    UNIQUE KEY `UNIQ_497B315EE7927C74` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==================== Table Véhicules ====================
CREATE TABLE `vehicules` (
    `ID_Vehicule` INT(11) NOT NULL AUTO_INCREMENT,
    `marque` VARCHAR(50) DEFAULT NULL,
    `modele` VARCHAR(50) DEFAULT NULL,
    `immatriculation` VARCHAR(20) NOT NULL UNIQUE,
    `type` VARCHAR(50) DEFAULT NULL,
    `capacite` FLOAT DEFAULT NULL,
    `statut` VARCHAR(50) DEFAULT 'disponible',
    `ID_Livreur` INT(11) DEFAULT NULL,
    `ID_Administrateur` INT(11) DEFAULT NULL,
    PRIMARY KEY (`ID_Vehicule`),
    FOREIGN KEY (`ID_Livreur`) REFERENCES `utilisateurs`(`ID_Utilisateur`) ON DELETE SET NULL,
    FOREIGN KEY (`ID_Administrateur`) REFERENCES `utilisateurs`(`ID_Utilisateur`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==================== Table Colis ====================
CREATE TABLE `colis` (
    `ID_Colis` INT(11) NOT NULL AUTO_INCREMENT,
    `description` TEXT DEFAULT NULL,
    `articles` TEXT DEFAULT NULL,
    `adresseDestination` TEXT NOT NULL,
    `dateCreation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `dateExpedition` TIMESTAMP NULL DEFAULT NULL,
    `poids` FLOAT DEFAULT NULL,
    `dimensions` VARCHAR(100) DEFAULT NULL,
    `statut` VARCHAR(50) DEFAULT 'en_attente',
    `ID_Expediteur` INT(11) NOT NULL,
    `ID_Destinataire` INT(11) NOT NULL,
    PRIMARY KEY (`ID_Colis`),
    FOREIGN KEY (`ID_Expediteur`) REFERENCES `utilisateurs`(`ID_Utilisateur`) ON DELETE CASCADE,
    FOREIGN KEY (`ID_Destinataire`) REFERENCES `utilisateurs`(`ID_Utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==================== Table Livraisons ====================
CREATE TABLE `livraisons` (
    `ID_Livraison` INT(11) NOT NULL AUTO_INCREMENT,
    `statut` VARCHAR(50) DEFAULT 'en_cours',
    `dateDebut` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `dateFin` TIMESTAMP NULL DEFAULT NULL,
    `distanceKm` FLOAT DEFAULT NULL,
    `payment` FLOAT DEFAULT NULL,
    `total` FLOAT DEFAULT NULL,
    `methodePaiement` VARCHAR(50) DEFAULT NULL,
    `ID_Colis` INT(11) NOT NULL,
    `ID_Livreur` INT(11) NOT NULL,
    PRIMARY KEY (`ID_Livraison`),
    FOREIGN KEY (`ID_Colis`) REFERENCES `colis`(`ID_Colis`) ON DELETE CASCADE,
    FOREIGN KEY (`ID_Livreur`) REFERENCES `utilisateurs`(`ID_Utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==================== Table Factures ====================
CREATE TABLE `factures` (
    `ID_Facture` INT(11) NOT NULL AUTO_INCREMENT,
    `numero` VARCHAR(50) NOT NULL UNIQUE,
    `dateEmission` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `montantHT` FLOAT NOT NULL,
    `montantTTC` FLOAT NOT NULL,
    `tva` FLOAT DEFAULT 0.0,
    `statut` VARCHAR(50) DEFAULT 'emise',
    `ID_Livraison` INT(11) NOT NULL,
    PRIMARY KEY (`ID_Facture`),
    FOREIGN KEY (`ID_Livraison`) REFERENCES `livraisons`(`ID_Livraison`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==================== Table Récompenses ====================
CREATE TABLE `recompenses` (
    `ID_Recompense` INT(11) NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(50) DEFAULT NULL,
    `valeur` FLOAT DEFAULT NULL,
    `dateObtention` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `description` TEXT DEFAULT NULL,
    `seuil` INT(11) DEFAULT NULL,
    `ID_Livreur` INT(11) NOT NULL,
    `ID_Facture` INT(11) DEFAULT NULL,
    PRIMARY KEY (`ID_Recompense`),
    FOREIGN KEY (`ID_Livreur`) REFERENCES `utilisateurs`(`ID_Utilisateur`) ON DELETE CASCADE,
    FOREIGN KEY (`ID_Facture`) REFERENCES `factures`(`ID_Facture`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==================== Table Devis ====================
CREATE TABLE `devis` (
    `ID_Devis` INT(11) NOT NULL AUTO_INCREMENT,
    `montantEstime` FLOAT NOT NULL,
    `dateCreation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `validite` INT(11) DEFAULT 30,
    `ID_Entreprise` INT(11) NOT NULL,
    `ID_Livraison` INT(11) DEFAULT NULL,
    PRIMARY KEY (`ID_Devis`),
    FOREIGN KEY (`ID_Entreprise`) REFERENCES `utilisateurs`(`ID_Utilisateur`) ON DELETE CASCADE,
    FOREIGN KEY (`ID_Livraison`) REFERENCES `livraisons`(`ID_Livraison`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==================== Table Publications ====================
CREATE TABLE `publications` (
    `ID_Publication` INT(11) NOT NULL AUTO_INCREMENT,
    `titre` VARCHAR(200) NOT NULL,
    `contenu` TEXT DEFAULT NULL,
    `datePublication` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `statut` VARCHAR(50) DEFAULT 'active',
    `ID_Auteur` INT(11) NOT NULL,
    PRIMARY KEY (`ID_Publication`),
    FOREIGN KEY (`ID_Auteur`) REFERENCES `utilisateurs`(`ID_Utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==================== Table Commentaires ====================
CREATE TABLE `commentaires` (
    `ID_Commentaire` INT(11) NOT NULL AUTO_INCREMENT,
    `contenu` TEXT NOT NULL,
    `dateCreation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ID_Publication` INT(11) NOT NULL,
    `ID_Auteur` INT(11) NOT NULL,
    PRIMARY KEY (`ID_Commentaire`),
    FOREIGN KEY (`ID_Publication`) REFERENCES `publications`(`ID_Publication`) ON DELETE CASCADE,
    FOREIGN KEY (`ID_Auteur`) REFERENCES `utilisateurs`(`ID_Utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==================== Table Réclamations ====================
CREATE TABLE `reclamations` (
    `ID_Reclamation` INT(11) NOT NULL AUTO_INCREMENT,
    `sujet` VARCHAR(200) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `dateCreation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `statut` VARCHAR(50) DEFAULT 'ouverte',
    `priorite` VARCHAR(50) DEFAULT 'normale',
    `ID_Livreur` INT(11) NOT NULL,
    `ID_Administrateur` INT(11) DEFAULT NULL,
    PRIMARY KEY (`ID_Reclamation`),
    FOREIGN KEY (`ID_Livreur`) REFERENCES `utilisateurs`(`ID_Utilisateur`) ON DELETE CASCADE,
    FOREIGN KEY (`ID_Administrateur`) REFERENCES `utilisateurs`(`ID_Utilisateur`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==================== Table Réponses ====================
CREATE TABLE `reponses` (
    `ID_Reponse` INT(11) NOT NULL AUTO_INCREMENT,
    `contenu` TEXT NOT NULL,
    `dateReponse` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ID_Reclamation` INT(11) NOT NULL,
    `ID_Administrateur` INT(11) NOT NULL,
    PRIMARY KEY (`ID_Reponse`),
    FOREIGN KEY (`ID_Reclamation`) REFERENCES `reclamations`(`ID_Reclamation`) ON DELETE CASCADE,
    FOREIGN KEY (`ID_Administrateur`) REFERENCES `utilisateurs`(`ID_Utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==================== Index pour améliorer les performances ====================
CREATE INDEX idx_utilisateur_role ON utilisateurs(role);
CREATE INDEX idx_colis_statut ON colis(statut);
CREATE INDEX idx_colis_expediteur ON colis(ID_Expediteur);
CREATE INDEX idx_colis_destinataire ON colis(ID_Destinataire);
CREATE INDEX idx_livraison_statut ON livraisons(statut);
CREATE INDEX idx_livraison_livreur ON livraisons(ID_Livreur);
CREATE INDEX idx_facture_numero ON factures(numero);
CREATE INDEX idx_publication_date ON publications(datePublication);
CREATE INDEX idx_reclamation_statut ON reclamations(statut);
CREATE INDEX idx_vehicule_statut ON vehicules(statut);

-- ==================== Données de test (optionnel) ====================
INSERT INTO `utilisateurs` (`email`, `nom`, `prenom`, `telephone`, `mot_de_passe`, `role`) VALUES
('admin@trackpack.com', 'Admin', 'Super', '0123456789', '$2y$10$example_hash', 'Administrateur'),
('livreur1@trackpack.com', 'Dupont', 'Jean', '0612345678', '$2y$10$example_hash', 'Livreur'),
('entreprise@trackpack.com', 'LogiExpress', 'SA', '0145678901', '$2y$10$example_hash', 'Entreprise'),
('client@trackpack.com', 'Martin', 'Sophie', '0698765432', '$2y$10$example_hash', 'Client');