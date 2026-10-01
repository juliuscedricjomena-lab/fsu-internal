# Phase 1: Quick Commands Reference

## Phase 1 Tasks - Commands to Run

Execute these commands in order to complete Phase 1.

---

## Task 4: Database Schema & Migrations

```bash
php artisan make:migration create_roles_table
php artisan make:migration create_permissions_table
php artisan make:migration create_role_has_permissions_table
php artisan migrate
```

### Verify Database
```bash
# Via PhpMyAdmin: http://localhost:8080
# MySQL direct:
mysql -h127.0.0.1 -uroot -proot_secret fsu_internal -e "SHOW TABLES;"
```

---

## Task 5: Authentication System (Sanctum)

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
php artisan make:controller AuthController
```

---

## Task 6: Role-Based Access Control (RBAC)

```bash
php artisan make:model Role -m
php artisan make:model Permission -m
php artisan make:seeder RoleSeeder
php artisan make:seeder PermissionSeeder
php artisan db:seed
```

---

## Task 7: Landing Page & Module Access UI

```bash
php artisan make:controller DashboardController
```

Then create Vue components in resources/js/Pages/

---

## Task 8: Pinia Store Setup

Already installed in Task 2. Create stores in:
- resources/js/Stores/auth.js
- resources/js/Stores/ui.js

---

## Task 9: Development Workflow & Git Setup

```bash
git init
git add .
git commit -m "Initial commit: Phase 1 setup"
git remote add origin <repo-url>
git push -u origin main
```

---

## Development Servers (Run Simultaneously)

### Terminal 1: Frontend Vite Dev Server
```bash
npm run dev
```

### Terminal 2: Backend Laravel Server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

---

## Access Points After Setup

```
✅ Main App:        http://localhost:8000
✅ Vite Dev:        http://localhost:5173
✅ PhpMyAdmin:      http://localhost:8080
✅ MySQL:           localhost:3306
✅ Redis:           localhost:6379
```

---

## Success Checklist

After completing Phase 1, verify:

- [ ] `docker-compose ps` shows 5 healthy services
- [ ] `http://localhost:8000` loads without errors
- [ ] User can login at `/login`
- [ ] Dashboard displays after login
- [ ] Database has 7 roles in `roles` table
- [ ] `npm run dev` shows no errors
- [ ] `php artisan test` passes
- [ ] Git repository has commits
- [ ] All documentation is complete

---

## Next: Phase 2

When Phase 1 ✅ Complete, follow Phase 2 spec

