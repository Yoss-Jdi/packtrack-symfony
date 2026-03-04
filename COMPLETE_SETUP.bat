@echo off
REM Complete Setup Script for PackTrack (Windows)
REM This will reset database and create admin account

echo ╔══════════════════════════════════════════════════════════════╗
echo ║         PackTrack - Complete Setup Script                   ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

REM Check if composer dependencies are installed
if not exist "vendor\" (
    echo Installing Composer dependencies...
    composer install
    echo.
)

REM Step 1: Drop the existing database
echo [1/6] Dropping existing database...
php bin/console doctrine:database:drop --force --if-exists
echo      ✓ Database dropped
echo.

REM Step 2: Create new database
echo [2/6] Creating new database...
php bin/console doctrine:database:create
if %errorlevel% neq 0 (
    echo ERROR: Failed to create database. Check your .env file!
    echo.
    echo Make sure DATABASE_URL is configured correctly:
    echo DATABASE_URL="mysql://root:password@127.0.0.1:3306/PackTrackDB"
    echo.
    pause
    exit /b 1
)
echo      ✓ Database created
echo.

REM Step 3: Run migrations
echo [3/6] Running migrations...
php bin/console doctrine:migrations:migrate --no-interaction
if %errorlevel% neq 0 (
    echo ERROR: Failed to run migrations
    pause
    exit /b 1
)
echo      ✓ Migrations executed
echo.

REM Step 4: Validate schema
echo [4/6] Validating database schema...
php bin/console doctrine:schema:validate
echo.

REM Step 5: Clear cache
echo [5/6] Clearing cache...
php bin/console cache:clear
echo      ✓ Cache cleared
echo.

REM Step 6: Create admin account
echo [6/6] Creating admin account...
echo.
php bin/console app:create-admin
echo.

echo ╔══════════════════════════════════════════════════════════════╗
echo ║         Setup completed successfully!                        ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo Your database is ready with the following tables:
echo   - utilisateurs (users)
echo   - vehicules (vehicles)
echo   - techniciens (technicians)
echo   - factures_maintenance (NEW - maintenance invoices)
echo   - colis, livraisons, factures, publications, etc.
echo.
echo Next steps:
echo   1. Start server: symfony server:start
echo      OR: php -S localhost:8000 -t public
echo   2. Login at: http://localhost:8000/login
echo   3. Go to: Vehicles → Liste
echo.
pause
