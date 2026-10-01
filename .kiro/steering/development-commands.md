# Development Commands Guide

## Quick Reference - Copy & Paste Commands

---

## 🚀 Initial Setup (Run Once)

### 1. Generate Application Key
```bash
php artisan key:generate
```

### 2. Install NPM Dependencies
```bash
npm install
```

### 3. Install Backend Dependencies
```bash
composer install
```

---

## 🐳 Docker Commands

### Start All Services
```bash
docker compose up -d --build
```

### Stop All Services
```bash
docker compose down
```

### View Logs
```bash
docker compose logs -f app
docker compose logs -f mysql
docker compose logs -f redis
```

### Check Services
```bash
docker compose ps
```

---

## 🗄️ Database Commands

### Run Migrations
```bash
php artisan migrate
```

### Fresh Database with Seeds
```bash
php artisan migrate:fresh --seed
```

### Create New Migration
```bash
php artisan make:migration create_transactions_table
```

### Create Model with Migration
```bash
php artisan make:model Transaction -m
```

### Seed Database
```bash
php artisan db:seed
```

---

## 🎨 Frontend Build Commands

### Development with Hot Reload
```bash
npm run dev
```

### Production Build
```bash
npm run build
```

---

## ⚙️ Laravel Artisan Commands

### Interactive Tinker Shell
```bash
php artisan tinker
```

### View All Routes
```bash
php artisan route:list
```

### Generate Key
```bash
php artisan key:generate
```

### Cache Configuration
```bash
php artisan config:cache
php artisan route:cache
```

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 📦 Composer Commands

### Install Dependencies
```bash
composer install
```

### Update Dependencies
```bash
composer update
```

### Install Single Package
```bash
composer require package/name
```

### Require Dev Package
```bash
composer require package/name --dev
```

---

## 📦 NPM Commands

### Install Dependencies
```bash
npm install
```

### Add Package
```bash
npm install package-name
```

### Add Dev Package
```bash
npm install package-name --save-dev
```

---

## 🔄 Git Commands

### Check Status
```bash
git status
```

### Add All Changes
```bash
git add .
```

### Commit Changes
```bash
git commit -m "message"
```

### Create New Branch
```bash
git checkout -b feature/name
```

### Push to Remote
```bash
git push origin feature/name
```

---

## 🚀 Development Workflow (Daily)

### Start of Day
```bash
# Terminal 1: Frontend
npm run dev

# Terminal 2: Backend
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 🐛 Debugging

### View Application Logs
```bash
tail -f storage/logs/laravel.log
```

### Check PHP Version
```bash
php --version
```

### Check Laravel Version
```bash
php artisan --version
```

---

## 📊 Database Access

### MySQL Direct
```bash
mysql -h127.0.0.1 -uroot -proot_secret fsu_internal
```

### Via PhpMyAdmin
```
Open: http://localhost:8080
User: laravel
Password: secret
```

---

## 🆘 Common Issues & Fixes

### Port Already in Use
```bash
# Kill process on port 8000
lsof -ti:8000 | xargs kill -9
```

### Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Rebuild Docker Images
```bash
docker compose down
docker compose up -d --build
```

---

## 📱 Access Points

```
Main Application:     http://localhost:8000
Frontend Dev Server:  http://localhost:5173
PhpMyAdmin:           http://localhost:8080
MySQL:                localhost:3306
Redis:                localhost:6379
```

