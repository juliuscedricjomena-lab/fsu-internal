# FSU Internal Finance System - Setup Guide

Complete step-by-step guide for setting up FSU Internal.

## Prerequisites

### System Requirements

- **PHP**: 8.5 or higher
- **Node.js**: 18+ with npm
- **Docker**: Latest version
- **Docker Compose**: 2.0+
- **Git**: 2.30+
- **RAM**: Minimum 4GB (8GB recommended)
- **Disk Space**: 5GB minimum

### Installation Commands

#### macOS
```bash
# Install PHP 8.5
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"

# Install Homebrew if not installed
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install Node.js and npm
brew install node

# Install Docker
brew install --cask docker
```

#### Linux (Ubuntu/Debian)
```bash
# Install PHP 8.5
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"

# Install Node.js and npm
sudo apt-get update
sudo apt-get install nodejs npm

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
```

#### Windows (PowerShell)
```powershell
# Install PHP 8.5
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))

# Install Node.js (via chocolatey)
choco install nodejs

# Install Docker Desktop
choco install docker-desktop
```

## Setup Steps

### Step 1: Clone Repository

```bash
cd /home/julius/projects
git clone https://github.com/your-org/fsu-internal.git
cd fsu-internal
```

Or if already in project directory:
```bash
cd /home/julius/projects/fsu-internal
```

### Step 2: Install Dependencies

#### Backend
```bash
# Install PHP dependencies
composer install

# If composer is not installed
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### Frontend
```bash
# Install Node dependencies
npm install

# Or using yarn if preferred
yarn install
```

### Step 3: Environment Configuration

```bash
# Copy example environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Output should show: Application key set successfully ✓
```

#### Configure .env

Edit `.env` and set these key variables:

```env
# Application
APP_NAME="FSU Internal"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (Docker MySQL)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=fsu_internal
DB_USERNAME=root
DB_PASSWORD=root

# Cache (Docker Redis)
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Session
SESSION_DRIVER=cookie

# Mail (use Mailtrap for development)
MAIL_MAILER=mailtrap
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
```

### Step 4: Start Docker Services

```bash
# Start all services in background
docker compose up -d

# Verify all services are running
docker compose ps

# Output should show:
# NAME          STATUS
# fsu-app       Up
# fsu-mysql     Up (healthy)
# fsu-redis     Up
# fsu-node      Up
# fsu-phpmyadmin Up
```

Wait 10-15 seconds for MySQL to be fully ready:
```bash
sleep 15
```

### Step 5: Database Setup

```bash
# Run migrations and seeders
php artisan migrate:fresh --seed

# Output should show all migrations completed and seeders run successfully
```

#### Verify Database

```bash
# Connect to MySQL
docker compose exec mysql mysql -u root -p root -e "USE fsu_internal; SHOW TABLES;"

# Should display:
# Tables_in_fsu_internal
# cache
# cache_locks
# jobs
# migrations
# permissions
# personal_access_tokens
# role_has_permissions
# roles
# users
```

### Step 6: Build Frontend Assets

```bash
# Build Tailwind CSS and compile assets
npm run build

# For development with hot reload (see Step 7)
```

### Step 7: Start Development Servers

**Terminal 1 - Laravel Server**
```bash
php artisan serve

# Output:
# INFO  Server running on [http://127.0.0.1:8000]
```

**Terminal 2 - Vite Development Server**
```bash
npm run dev

# Output:
# VITE v... ready in XXX ms
# ➜ Local: http://localhost:5173/
```

### Step 8: Access Application

1. **Main Application**: http://localhost:8000
2. **Database Management**: http://localhost:8080
   - Username: `root`
   - Password: `root`

## Verification Checklist

- [ ] Docker services running: `docker compose ps` (all showing "Up")
- [ ] Database connected: `php artisan tinker` → `DB::connection()->getPDO()`
- [ ] Test users seeded: Check PHPMyAdmin users table
- [ ] Frontend loads: http://localhost:8000 loads without errors
- [ ] Login works: Try admin@fsu.local / password123
- [ ] Dashboard loads: See modules after login
- [ ] Vite hot reload: Modify a Vue file, browser auto-refreshes

## Test Accounts

After running seeders, these accounts are available:

```
Email: admin@fsu.local
Password: password123
Role: ADMINISTRATOR
Permissions: All

Email: collector@fsu.local
Password: password123
Role: COLLECTION_STAFF
Permissions: Collection, Reports

Email: finance@fsu.local
Password: password123
Role: FINANCE_STAFF
Permissions: Finance, Reports

Email: viewer@fsu.local
Password: password123
Role: VIEWER
Permissions: View-only access
```

## Troubleshooting

### MySQL Connection Issues

```bash
# Check MySQL container logs
docker compose logs mysql

# Restart MySQL
docker compose restart mysql

# Wait and retry
sleep 10
php artisan migrate:fresh --seed
```

### Port Already in Use

```bash
# Check what's using port 8000
lsof -i :8000

# Use different port
php artisan serve --port=8001

# For Vite (port 5173)
npm run dev -- --port 5174
```

### Composer Timeout

```bash
# Increase composer timeout
composer install --no-interaction --prefer-dist --no-progress --with-all-dependencies
```

### Database Not Found

```bash
# Verify MySQL is running
docker compose logs mysql

# Check database exists
docker compose exec mysql mysql -u root -p root -e "SHOW DATABASES;"

# If missing, run migrations
php artisan migrate:fresh --seed
```

### Frontend Not Loading

```bash
# Clear Vite cache
rm -rf node_modules/.vite

# Rebuild
npm run build

# Restart dev server
npm run dev
```

### Cache Issues

```bash
# Clear all Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Clear Redis cache
docker compose exec redis redis-cli FLUSHALL
```

## Docker Service Management

### View Logs

```bash
# All services
docker compose logs

# Specific service
docker compose logs mysql
docker compose logs fsu-app

# Follow logs in real-time
docker compose logs -f
```

### Stop Services

```bash
# Stop all (keeps data)
docker compose stop

# Down (removes containers but keeps data)
docker compose down

# Down and remove volumes (DELETES DATA)
docker compose down -v
```

### Restart Services

```bash
# Restart all
docker compose restart

# Restart specific service
docker compose restart mysql
```

### Access Container Shell

```bash
# PHP container
docker compose exec fsu-app bash

# MySQL
docker compose exec mysql bash

# Redis
docker compose exec redis bash
```

## Development Workflow

### Before Committing

```bash
# Run linter
npm run lint

# Run tests
php artisan test

# Check code style
./vendor/bin/pint --test

# Commit changes
git add .
git commit -m "feat(scope): description"
```

### Create Feature Branch

```bash
# Create branch
git checkout -b feature/your-feature-name

# Make changes and commit
git add .
git commit -m "feat(scope): description"

# Push to remote
git push -u origin feature/your-feature-name
```

### Merge Pull Request

```bash
# After PR approved
git checkout master
git pull origin master
git merge feature/your-feature-name
git push origin master
```

## Database Migrations

### Create New Migration

```bash
php artisan make:migration create_tablename_table
```

### Run Migrations

```bash
# All pending migrations
php artisan migrate

# Specific batch
php artisan migrate --step

# Rollback last
php artisan migrate:rollback

# Rollback all
php artisan migrate:reset

# Refresh (rollback + migrate + seed)
php artisan migrate:refresh --seed

# Fresh (drop + migrate + seed)
php artisan migrate:fresh --seed
```

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| "SQLSTATE[HY000] [2002] Connection refused" | Ensure MySQL is running: `docker compose up -d mysql` |
| "Module not found" | Run `npm install` again |
| "Column not found" | Run migrations: `php artisan migrate:fresh --seed` |
| "Port 8000 in use" | Use different port: `php artisan serve --port=8001` |
| "Slow npm install" | Clear cache: `npm cache clean --force` |

## Next Steps

1. Read [ARCHITECTURE.md](./ARCHITECTURE.md) for system design details
2. Review [.kiro/specs/phase-1-foundation-setup.md](./.kiro/specs/phase-1-foundation-setup.md) for specifications
3. Check [.kiro/steering/git-workflow.md](./.kiro/steering/git-workflow.md) for contribution guidelines
4. Start Phase 2 development

---

**Last Updated**: October 1, 2026
**Version**: Phase 1 Foundation
