#!/bin/bash
# Database Reset Script for PackTrack
# This script will drop the old database and create a fresh one

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║         PackTrack - Database Reset Script                   ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

# Step 1: Drop the existing database
echo "Step 1: Dropping existing database..."
php bin/console doctrine:database:drop --force --if-exists
echo "✓ Database dropped"
echo ""

# Step 2: Create new database
echo "Step 2: Creating new database..."
php bin/console doctrine:database:create
echo "✓ Database created"
echo ""

# Step 3: Run migrations
echo "Step 3: Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction
echo "✓ Migrations executed"
echo ""

# Step 4: Clear cache
echo "Step 4: Clearing cache..."
php bin/console cache:clear
echo "✓ Cache cleared"
echo ""

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║         Database reset completed successfully!               ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""
echo "Next steps:"
echo "1. Create admin account: php bin/console app:create-admin"
echo "2. Login at: http://localhost:8000/login"
echo ""
