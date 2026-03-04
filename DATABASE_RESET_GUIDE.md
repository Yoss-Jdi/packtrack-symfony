# Database Reset Guide

## Quick Method: Use the Script

### For Windows:
```bash
reset_database.bat
```

### For Linux/Mac:
```bash
chmod +x reset_database.sh
./reset_database.sh
```

---

## Manual Method: Step by Step

### Step 1: Drop the Old Database
```bash
php bin/console doctrine:database:drop --force
```

### Step 2: Create New Database
```bash
php bin/console doctrine:database:create
```

### Step 3: Run All Migrations
```bash
php bin/console doctrine:migrations:migrate
```
When prompted, type `yes` or use `--no-interaction` flag:
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### Step 4: Clear Cache
```bash
php bin/console cache:clear
```

### Step 5: Create Admin Account
```bash
php bin/console app:create-admin
```

---

## What Gets Created

The migrations will create these tables:

1. **utilisateurs** - Users table
2. **vehicules** - Vehicles table
3. **techniciens** - Technicians table
4. **factures** - Invoices for deliveries
5. **factures_maintenance** - NEW: Maintenance invoices
6. **livraisons** - Deliveries table
7. **colis** - Packages table
8. **publications** - Forum publications
9. **commentaires** - Comments
10. **notifications** - Notifications
11. **recompenses** - Rewards
12. And other related tables...

---

## Troubleshooting

### Error: "Database does not exist"
This is normal if the database was already deleted. Continue to Step 2.

### Error: "Access denied"
Check your `.env` file database credentials:
```
DATABASE_URL="mysql://username:password@127.0.0.1:3306/PackTrackDB"
```

### Error: "No migrations to execute"
Your migrations folder might be empty. Check `migrations/` directory.

### Error: "Table already exists"
The database wasn't properly dropped. Try:
```bash
php bin/console doctrine:database:drop --force --if-exists
```

### Error: "Dependencies are missing"
Run composer install first:
```bash
composer install
```

---

## Verify Database Creation

### Check tables were created:
```bash
php bin/console doctrine:schema:validate
```

### Or connect to MySQL:
```bash
mysql -u root -p
```
```sql
USE PackTrackDB;
SHOW TABLES;
```

You should see all tables including `factures_maintenance`.

---

## After Reset

1. **Create admin account:**
   ```bash
   php bin/console app:create-admin
   ```

2. **Login to application:**
   - URL: http://localhost:8000/login
   - Use the credentials you just created

3. **Test the system:**
   - Go to Vehicles → Liste
   - Create a test vehicle
   - Test the maintenance invoice feature

---

## Database Configuration

Make sure your `.env` file has correct database settings:

```env
# .env
DATABASE_URL="mysql://root:password@127.0.0.1:3306/PackTrackDB?serverVersion=8.0&charset=utf8mb4"
```

Adjust:
- `root` → your MySQL username
- `password` → your MySQL password
- `127.0.0.1:3306` → your MySQL host and port
- `PackTrackDB` → your database name
- `serverVersion=8.0` → your MySQL version

---

## Complete Fresh Start Command Sequence

```bash
# 1. Drop old database
php bin/console doctrine:database:drop --force --if-exists

# 2. Create new database
php bin/console doctrine:database:create

# 3. Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# 4. Clear cache
php bin/console cache:clear

# 5. Create admin
php bin/console app:create-admin

# 6. Start server (if needed)
symfony server:start
# or
php -S localhost:8000 -t public
```

Done! Your database is now fresh and ready to use.
