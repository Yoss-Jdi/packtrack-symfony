-- Table for vehicle maintenance invoices
-- Run this SQL if you prefer manual database creation

CREATE TABLE IF NOT EXISTS factures_maintenance (
    ID_Facture_Maintenance INT AUTO_INCREMENT NOT NULL,
    vehicule_id INT NOT NULL,
    technicien_id INT DEFAULT NULL,
    numero VARCHAR(100) NOT NULL,
    dateEmission DATETIME NOT NULL,
    montantHT DECIMAL(10, 2) NOT NULL,
    montantTTC DECIMAL(10, 2) NOT NULL,
    tauxTVA DECIMAL(5, 2) NOT NULL,
    descriptionTravaux TEXT NOT NULL,
    fournisseur VARCHAR(255) DEFAULT NULL,
    pieceChangees TEXT DEFAULT NULL,
    PRIMARY KEY (ID_Facture_Maintenance),
    UNIQUE KEY UNIQ_FACTURE_MAINT_NUMERO (numero),
    KEY IDX_FACTURE_MAINT_VEHICULE (vehicule_id),
    KEY IDX_FACTURE_MAINT_TECHNICIEN (technicien_id),
    CONSTRAINT FK_FACTURE_MAINT_VEHICULE 
        FOREIGN KEY (vehicule_id) 
        REFERENCES vehicules (ID_Vehicule) 
        ON DELETE CASCADE,
    CONSTRAINT FK_FACTURE_MAINT_TECHNICIEN 
        FOREIGN KEY (technicien_id) 
        REFERENCES techniciens (ID_Technicien) 
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
