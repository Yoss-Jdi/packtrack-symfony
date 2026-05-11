# 📦 PackTrack - Delivery Management System

A comprehensive delivery management system featuring real-time package tracking, fraud detection, and delivery prediction powered by machine learning.

**Version**: 1.0  
**Last Updated**: May 2026

---

## 📋 Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Installation & Setup](#installation--setup)
- [Services](#services)
- [Database](#database)
- [Development](#development)
- [Docker Setup](#docker-setup)

---

## 🎯 Overview

**PackTrack** is a full-stack delivery management platform designed for courier companies and logistics providers. It combines:

- **Web Application**: Symfony 7.4-based backend with public user portal and admin dashboard
- **Fraud Detection AI**: FastAPI service using deep learning to detect fraudulent invoices
- **ML Prediction Engine**: Flask/ML service for delivery time and distance prediction
- **Real-time Tracking**: Live package status updates and delivery timeline
- **Admin Panel**: Comprehensive management tools for operations

The system supports multiple user roles: Admins, Delivery Personnel, Companies, and Clients, each with tailored dashboards and permissions.

---

## 🏗️ Architecture

```
PackTrack
├── Symfony Application (Core Web App)
│   ├── Public Frontend (Customer Portal)
│   ├── Admin Backend (Operations Dashboard)
│   └── REST API (Internal & External)
│
├── ia_service (FastAPI - Fraud Detection)
│   └── Invoice Verification using Autoencoder
│
├── ml-service (Flask - Prediction Engine)
│   ├── Delivery Duration Prediction
│   └── Distance Calculation
│
└── Database (MySQL/MariaDB)
    └── Relational Schema
```

---

## 🚀 Features

### Public Frontend (Customer Portal)
- **Real-time Package Tracking**: Live status updates for shipped packages
- **Delivery Timeline**: Visual representation of delivery progress
- **Package Details**: Complete information about packages in transit
- **User Profile**: Account management and preferences

### Admin Dashboard (Backoffice)
- **Statistics Dashboard**: KPIs and performance metrics
- **Package Management**: Full CRUD operations for packages
- **Invoice Management**: Invoice handling with fraud detection integration
- **Vehicle Management**: Fleet and vehicle tracking
- **Community Forum**: Internal discussion forum for staff
- **Incident Management**: Report and manage delivery incidents
- **User Management**: Admin controls for user accounts and roles
- **Rewards System**: Incentive programs for delivery personnel

### Machine Learning Features
- **Fraud Detection**: AI-powered invoice anomaly detection
- **Delivery Prediction**: Estimated delivery times based on historical data
- **Distance Optimization**: Route and distance calculations using OpenRouteService API

---

## 🛠️ Technology Stack

### Backend
- **Framework**: Symfony 7.4
- **PHP Version**: >= 8.2
- **ORM**: Doctrine ORM 3.6+
- **Database**: MySQL/MariaDB
- **Mailer**: Brevo & Google Mail Integration
- **PDF Generation**: DOMPDF 3.1+
- **QR Code Generation**: Endroid QR Code Bundle

### Frontend
- **Templating**: Twig 3.x
- **CSS Framework**: Bootstrap 5.3+
- **JavaScript Framework**: Hotwired Stimulus 3.x
- **Asset Management**: Webpack Encore
- **Icons**: Font Awesome 6.5+
- **Calendar**: FullCalendar 6.x

### Machine Learning Services
- **IA Service (FastAPI)**
  - Framework: FastAPI 0.111+
  - ML: PyTorch 2.10+
  - Data Processing: Pandas, NumPy, Scikit-learn
  - PDF Processing: pdfplumber
  
- **ML Service (Flask)**
  - Framework: Flask
  - ML: Scikit-learn
  - Routing: OpenRouteService API
  - Database Connector: SQLAlchemy, PyMySQL

### DevOps
- **Containerization**: Docker & Docker Compose
- **Build Tools**: Webpack 5, Babel 7

---

## 📁 Project Structure

```
PackTrack/
├── src/                          # Symfony application source
│   ├── Controller/              # HTTP request handlers
│   ├── Entity/                  # Doctrine ORM entities
│   ├── Repository/              # Database queries
│   ├── Service/                 # Business logic
│   ├── Command/                 # CLI commands
│   ├── EventSubscriber/         # Event listeners
│   ├── Form/                    # Symfony forms
│   ├── Security/                # Security & authentication
│   └── Validator/               # Custom validators
│
├── templates/                    # Twig templates
│   ├── base.html.twig           # Base layout
│   ├── admin/                   # Admin pages
│   ├── front/                   # Public pages
│   ├── facture/                 # Invoice templates
│   ├── parcel/                  # Package templates
│   ├── vehicle/                 # Vehicle management
│   ├── forum/                   # Forum pages
│   ├── incident/                # Incident management
│   ├── profil/                  # User profiles
│   └── emails/                  # Email templates
│
├── assets/                       # Frontend assets
│   ├── css/                     # Stylesheets
│   ├── controllers/             # Stimulus controllers
│   └── vendor/                  # Third-party JS
│
├── config/                       # Symfony configuration
│   ├── services.yaml            # Service definitions
│   ├── security.yaml            # Security config
│   ├── routes.yaml              # Route definitions
│   └── packages/                # Bundle configurations
│
├── migrations/                   # Database migrations
├── public/                       # Web root
│   ├── index.php               # Entry point
│   └── uploads/                # User uploads
│
├── tests/                        # PHPUnit tests
│
├── ia_service/                   # Fraud Detection Service
│   ├── api.py                  # FastAPI application
│   ├── modeles/                # Trained ML models
│   └── requirements.txt        # Python dependencies
│
├── ml-service/                   # Prediction Service
│   ├── app.py                  # Flask application
│   ├── models/                 # ML model files
│   └── requirements.txt        # Python dependencies
│
├── ml/                           # Legacy ML scripts
│   ├── predict_ca.py           # CA prediction
│   └── train_ca.py             # Model training
│
├── bin/
│   ├── console                 # Symfony CLI
│   └── phpunit                 # Test runner
│
├── compose.yaml                # Docker Compose config
├── webpack.config.js           # Webpack configuration
└── package.json               # NPM dependencies
```

---

## 📦 Database Structure

### Core Tables
- **utilisateurs**: User accounts (Admins, Delivery Staff, Companies, Clients)
- **colis**: Package records with status and details
- **livraisons**: Delivery information linked to packages
- **factures**: Invoice management and tracking
- **vehicules**: Fleet vehicles and delivery vehicles
- **devis**: Quotation/estimates management

### Support Tables
- **recompenses**: Rewards and incentive system
- **publications**: Forum posts
- **commentaires**: Comments on forum posts
- **reclamations**: Complaint management
- **reponses**: Complaint responses

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- MySQL/MariaDB 5.7+
- Node.js 16+
- Python 3.9+ (for ML services)
- Composer
- Docker & Docker Compose (optional)

### Step 1: Clone & Install Dependencies

```bash
# Clone the repository
git clone <repository-url>
cd PackTrack

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Step 2: Environment Configuration

```bash
# Copy environment template
cp .env.example .env

# Edit .env with your configuration
# - Database credentials
# - Mail service credentials
# - API keys (OpenRouteService, etc.)
```

### Step 3: Database Setup

```bash
# Create database and run migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Load initial data (optional)
php bin/console doctrine:fixtures:load
```

### Step 4: Build Assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

### Step 5: Setup ML Services

```bash
# Fraud Detection Service
cd ia_service
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
pip install -r requirements.txt

# Prediction Service (in separate terminal)
cd ml-service
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
pip install -r requirements.txt
```

### Step 6: Start Application

```bash
# Start Symfony dev server
symfony serve

# In separate terminals:
# Start Fraud Detection Service
cd ia_service
uvicorn api:app --reload --port 8001

# Start Prediction Service
cd ml-service
python app.py  # runs on port 5000
```

Access the application at `http://localhost:8000`

---

## 🤖 Services

### IA Service (Fraud Detection)
**Location**: `/ia_service`  
**Framework**: FastAPI  
**Port**: 8001

**Purpose**: Detects fraudulent invoices using an Autoencoder neural network model.

**Key Files**:
- `api.py`: Main FastAPI application
- `modeles/input_dim.pkl`: Model input dimensions
- `modeles/scaler.pkl`: Data scaler
- `generer_facture_fraude.py`: Fraud invoice generator for testing

**API Endpoints**:
- `POST /verify`: Upload invoice PDF for fraud detection
- Returns anomaly score and fraud probability

### ML Service (Prediction Engine)
**Location**: `/ml-service`  
**Framework**: Flask  
**Port**: 5000

**Purpose**: Predicts delivery duration and distance for routes.

**Key Features**:
- Geocoding via OpenRouteService API
- Distance prediction model
- Delivery duration estimation
- Trained on historical Tunisian delivery data

**Models Used**:
- `model_distance.pkl`: Distance prediction
- `model_duree.pkl`: Duration prediction

---

## 🐳 Docker Setup

### Quick Start with Docker Compose

```bash
# Build and start all services
docker-compose up -d

# Run migrations
docker-compose exec app php bin/console doctrine:migrations:migrate

# Build assets
docker-compose exec app npm run build
```

**Services Started**:
- Symfony Application (Port 8000)
- MySQL Database (Port 3306)
- IA Service/FastAPI (Port 8001)
- ML Service/Flask (Port 5000)

### Configuration Files
- `compose.yaml`: Main Compose configuration
- `compose.override.yaml`: Development overrides

---

## 👨‍💻 Development

### Running Tests

```bash
# Run PHP unit tests
php bin/phpunit

# Run specific test file
php bin/phpunit tests/Service/MyServiceTest.php
```

### Database Resets

**Quick reset (automated)**:
```bash
# Windows
reset_database.bat

# Linux/Mac
chmod +x reset_database.sh
./reset_database.sh
```

**Manual reset**:
```bash
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### Asset Building

```bash
# Watch for changes (development)
npm run watch

# One-time dev build
npm run dev

# Production build
npm run build

# Dev server with hot reload
npm run dev-server
```

### Database Migrations

```bash
# Create new migration
php bin/console make:migration

# Run migrations
php bin/console doctrine:migrations:migrate

# Rollback last migration
php bin/console doctrine:migrations:migrate prev
```

---

## 📊 Key Features Deep Dive

### Real-time Package Tracking
- Packages can be tracked from dispatch to delivery
- Live status updates (Pending → In Transit → Delivered)
- GPS tracking for delivery vehicles
- Estimated delivery time calculation

### Admin Dashboard
- KPI cards showing total packages, completed deliveries, revenue
- Recent activity feed
- Performance charts and analytics
- User management interface

### Invoice Management with Fraud Detection
- Upload invoices as PDFs
- Automatic fraud detection using ML
- Flag suspicious invoices for review
- Archive and export capabilities

### Community Forum
- Staff discussion board
- Comment threads
- Category organization
- Moderation tools

---

## 🔒 Security

- CSRF protection on all forms
- SQL injection prevention via Doctrine ORM
- Password hashing using bcrypt
- Role-based access control (RBAC)
- Secure file upload handling
- CORS middleware on API services

---

## 📝 Notes

- All timestamps in database use UTC
- Database migrations are version-controlled
- ML models require retraining periodically with new data
- OpenRouteService API key required for distance calculations
- Email service requires Brevo or Google account credentials

---

## 📞 Support & Troubleshooting

### Common Issues

**Database Connection Error**:
```bash
# Verify database credentials in .env
# Restart database service if using Docker
docker-compose restart db
```

**Assets Not Loading**:
```bash
# Rebuild assets
npm run dev
```

**ML Services Not Responding**:
```bash
# Verify services are running on correct ports
# Check python dependencies are installed
pip install -r requirements.txt
```

---

## 📄 License

Proprietary - All rights reserved

---

## 👥 Contributors

PackTrack Development Team

---

**Last Updated**: May 2026

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