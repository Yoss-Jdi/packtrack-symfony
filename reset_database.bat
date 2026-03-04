@echo off
REM Database Reset Script for PackTrack (Windows)
REM This script will drop the old database and create a fresh one

echo ================================================================
echo          PackTrack - Database Reset Script
echo ================================================================
echo.

REM Step 1: Drop the existing database
echo Step 1: Dropping existing database...
php bin/console doctrine:database:drop --force --if-exists
if %errorlevel% neq 0 (
    echo ERROR: Failed to drop database
    pause
    exit /b 1
)
echo [OK] Database dropped
echo.

REM Step 2: Create new database
echo Step 2: Creating new database...
php bin/console doctrine:database:create
if %errorlevel% neq 0 (
    echo ERROR: Failed to create database
    pause
    exit /b 1
)
echo [OK] Database created
echo.

REM Step 3: Run migrations
echo Step 3: Running migrations...
php bin/console doctrine:migrations:migrate --no-interaction
if %errorlevel% neq 0 (
    echo ERROR: Failed to run migrations
    pause
    exit /b 1
)
echo [OK] Migrations executed
echo.

REM Step 4: Clear cache
echo Step 4: Clearing cache...
php bin/console cache:clear
if %errorlevel% neq 0 (
    echo ERROR: Failed to clear cache
    pause
    exit /b 1
)
echo [OK] Cache cleared
echo.

echo ================================================================
echo          Database reset completed successfully!
echo ================================================================
echo.
echo Next steps:
echo 1. Create admin account: php bin/console app:create-admin
echo 2. Login at: http://localhost:8000/login
echo.
pause
