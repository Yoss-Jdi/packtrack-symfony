# 📦 PackTrack - Gestion de livraison de colis

Application web de gestion de livraison de colis développée avec Symfony 7.1.

## 🚀 Fonctionnalités

### Front (Public)
- Suivi de colis en temps réel
- Timeline de livraison
- Détails du colis

### Admin (Backoffice)
- Dashboard avec statistiques
- Gestion des colis
- Gestion des factures
- Gestion des véhicules
- Forum communautaire
- Gestion des incidents

# 📦 Base de données TrackPack

## Structure de la base de données

Ce dossier contient les scripts SQL pour initialiser la base de données du projet PackTrack.

## Fichiers

- `trackpackdb.sql` : Script complet de création de la base de données avec structure et données de test

## Tables principales

- **utilisateurs** : Gestion des utilisateurs (Admin, Livreur, Entreprise, Client)
- **colis** : Gestion des colis
- **livraisons** : Gestion des livraisons
- **factures** : Gestion des factures
- **vehicules** : Gestion des véhicules
- **recompenses** : Système de récompenses pour les livreurs
- **devis** : Gestion des devis
- **publications** : Forum/Publications
- **commentaires** : Commentaires sur les publications
- **reclamations** : Gestion des réclamations
- **reponses** : Réponses aux réclamations

## 🛠️ Technologies

- **Backend** : Symfony 7.1
- **Frontend** : HTML5, CSS3, Twig
- **Icons** : Font Awesome 6.5

## 📦 Installation

### Prérequis
- PHP 8.2+
- Composer
- Symfony CLI
- Node.js & npm (pour les assets)

### Installation
```bash
# Cloner le projet
git clone https://github.com/ton-username/packtrack-symfony.git
cd packtrack-symfony

# Installer les dépendances PHP
composer install

# Installer les dépendances JS
npm install

# Compiler les assets
npm run dev

# Lancer le serveur
symfony server:start
```

## 🌐 URLs

- **Front** : http://127.0.0.1:8000/
- **Admin** : http://127.0.0.1:8000/admin/dashboard

## 📝 License

Ce projet est développé dans un cadre éducatif.