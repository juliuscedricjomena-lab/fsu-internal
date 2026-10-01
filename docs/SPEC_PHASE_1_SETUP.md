# SPEC: Phase 1 - Project Setup & Foundation

## Overview

**Phase**: 1 - Foundation & Infrastructure  
**Title**: Project Initialization, Docker Setup, and Core Configuration  
**Complexity**: Medium  
**Estimated Time**: 3-4 days  
**Dependencies**: None (first phase)

---

## Objectives

1. ✅ Initialize Laravel 13 project structure
2. ✅ Set up Docker environment (MySQL, Redis, PHP-FPM)
3. ✅ Configure Inertia.js + Vue 3 integration
4. ✅ Set up authentication system with Sanctum
5. ✅ Create database schema and migrations
6. ✅ Configure role-based access control foundation
7. ✅ Set up development workflow and Git

---

## Requirements

### Technical Stack (Confirmed)
- Laravel 13 (latest)
- Vue 3 (Composition API)
- Inertia.js
- MySQL 8.0+
- Redis 7+
- Docker & Docker Compose
- Tailwind CSS
- Pinia (state management)

### Environment
- Local development with Docker
- Production deployment on Laravel Cloud
- Git version control (GitHub)

---

## Design Details

### Application Architecture

```
fsu-internal/
├── app/                    # Laravel application logic
├── resources/
│   ├── js/                # Vue 3 components
│   └── css/               # Tailwind CSS
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/           # Initial data
├── docker/                # Docker configurations
├── routes/web.php         # Inertia routes
├── config/                # Configuration files
└── .env                   # Environment variables
```

### Authentication Flow

```
User Login
    ↓
POST /login (Laravel)
    ↓
Verify credentials (User model)
    ↓
Issue session (Laravel Sanctum)
    ↓
Redirect to Landing Page (Vue)
    ↓
Load authenticated state (Pinia store)
```

### Role-Based Access (Foundation)

**Roles to be created:**
1. **ALL_ACCESS** - Full system access
2. **ADMINISTRATOR** - Admin functions
3. **CO_ADMIN** - Limited admin
4. **COLLECTION_STAFF** - Collection module only
5. **FINANCE_STAFF** - Finance modules (Payroll, Remittance)
6. **DISBURSEMENT_OFFICER** - Disbursement module
7. **VIEWER** - Read-only access

**Middleware:**
- `auth` - Verify user is authenticated
- `role` - Verify user has required role
- `permission` - Verify user has specific permission

---

## Implementation Tasks

### Task 1: Laravel Project Initialization

**Objective**: Set up fresh Laravel 13 project with proper directory structure

**Steps:**
```bash
# Create project
composer create-project laravel/laravel fsu-internal

# Enter directory
cd fsu-internal

# Version should be Laravel 13.x
php artisan --version
```

**Key Configurations:**
- Set `APP_NAME=FSU Internal`
- Set `APP_URL=http://localhost:8000`
- Generate application key
- Set timezone to Asia/Manila

**Deliverable**: 
- Fresh Laravel 13 installation
- `.env` file configured for local development
- All Laravel directories accessible

---

### Task 2: Install & Configure Inertia.js + Vue 3

**Objective**: Integrate Inertia.js with Vue 3 for server-side rendering

**Steps:**
```bash
# Install Inertia Laravel adapter
composer require inertiajs/inertia-laravel

# Publish Inertia configuration
php artisan inertia:install --typescript

# Install Node dependencies
npm install
npm install @inertiajs/vue3 vue vue-router @headlessui/vue

# Install Tailwind CSS
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p

# Install additional UI tools
npm install @headlessui/vue pinia axios vue-toastification
```

**Configuration Files to Update:**
- `vite.config.js` - Vite build configuration
- `tailwind.config.js` - Tailwind CSS configuration
- `resources/js/app.js` - Vue 3 entry point
- `app/Http/Middleware/HandleInertiaRequests.php` - Inertia middleware

**Deliverable:**
- Inertia.js configured and running
- Vue 3 components can be rendered
- Vite dev server working on port 5173

---

### Task 3: Docker Setup

**Objective**: Create Docker environment for local development

**Files to Create:**
- `docker-compose.yml` - Service orchestration
- `Dockerfile` - PHP-FPM application container
- `docker/app/php.ini` - PHP configuration
- `docker/mysql/my.cnf` - MySQL configuration
- `docker/nginx/nginx.conf` - Nginx configuration

**Services to Configure:**
- Laravel App (PHP 8.3-FPM)
- MySQL 8.0
- Redis 7
- Node (for Vite dev server)
- PhpMyAdmin (for development)

**Deliverable:**
```bash
docker-compose up -d

# Verify all services are running
docker-compose ps

# App available at http://localhost:8000
# PhpMyAdmin available at http://localhost:8080
# Vite dev server at http://localhost:5173
```

---

### Task 4: Database Schema & Migrations

**Objective**: Create database tables with proper relationships

**Migrations to Create:**
1. `create_users_table.php` - User records
2. `create_roles_table.php` - Role definitions
3. `create_permissions_table.php` - Permission definitions
4. `create_role_has_permissions_table.php` - Role-Permission mapping
5. `create_role_has_users_table.php` - User-Role mapping
6. `create_audit_logs_table.php` - Audit trail

**Commands:**
```bash
php artisan make:migration create_roles_table
php artisan make:migration create_permissions_table
php artisan make:migration create_role_has_permissions_table
php artisan make:migration create_role_has_users_table

# Run migrations
php artisan migrate
```

**Key Tables:**
- `users` - User accounts with designation, rank
- `roles` - System roles (Administrator, Staff, etc.)
- `permissions` - Fine-grained permissions
- `audit_logs` - User action tracking
- `sessions` - User session data

**Deliverable:**
- All migrations created and run successfully
- Database schema matches design
- Foreign keys properly configured
- Indexes for performance optimization

---

### Task 5: Authentication System

**Objective**: Implement user authentication with Laravel Sanctum

**Files to Create/Modify:**
- `app/Models/User.php` - User model with roles relationship
- `app/Http/Controllers/AuthController.php` - Authentication logic
- `routes/web.php` - Authentication routes

**Features:**
- User registration (admin only for now)
- Login with email/password
- Logout
- Session management
- "Remember me" functionality

**Routes:**
```php
POST   /login           - User login
POST   /logout          - User logout
POST   /register        - User registration (admin)
GET    /user            - Get authenticated user
```

**Middleware:**
```php
middleware(['auth'])    - Requires authentication
middleware(['auth', 'role:administrator'])  - Role check
```

**Deliverable:**
- Authentication fully functional
- Users can log in/out
- Session persists across requests
- User data available in Vue components via `usePage()`

---

### Task 6: Role-Based Access Control (RBAC)

**Objective**: Create foundation for role and permission management

**Files to Create:**
- `app/Models/Role.php` - Role model
- `app/Models/Permission.php` - Permission model
- `app/Policies/UserPolicy.php` - Authorization policy
- `database/seeders/RoleSeeder.php` - Initial roles
- `database/seeders/PermissionSeeder.php` - Initial permissions

**Seeders to Create:**

```php
// Create 7 roles
- ALL_ACCESS
- ADMINISTRATOR
- CO_ADMIN
- COLLECTION_STAFF
- FINANCE_STAFF
- DISBURSEMENT_OFFICER
- VIEWER

// Create initial permissions (will expand in later phases)
- view_dashboard
- manage_users
- manage_roles
- view_collection
- create_transaction
- view_payroll
- upload_payslip
- view_remittance
- upload_remittance
- view_disbursement
- create_disbursement
```

**Middleware to Create:**
```php
class RoleMiddleware {
    // Check if user has required role
    // Redirect if not authorized
}

class PermissionMiddleware {
    // Check if user has specific permission
    // Redirect if not authorized
}
```

**Deliverable:**
- 7 roles created in database
- Initial permissions seeded
- Middleware functional for role/permission checking
- User-role relationships working

---

### Task 7: Landing Page & Module Access UI

**Objective**: Create "I UNDERSTAND" landing page showing available modules

**Vue Components to Create:**
- `resources/js/Pages/Auth/Login.vue` - Login form
- `resources/js/Pages/Dashboard/Landing.vue` - Landing page after login
- `resources/js/Components/Layout/AppLayout.vue` - Main application layout
- `resources/js/Components/Sidebar.vue` - Navigation sidebar
- `resources/js/Components/Header.vue` - Top header bar

**Features:**
1. **Login Page:**
   - Email/password form
   - "Remember me" checkbox
   - Error handling
   - Link to documentation

2. **Landing Page:**
   - "I UNDERSTAND" button
   - Display available modules based on user role
   - Module cards with icons
   - Brief descriptions

3. **Layout Components:**
   - Sidebar navigation
   - User profile dropdown
   - Logout button
   - Module selection

**Deliverable:**
- Login page fully functional
- Landing page displays after authentication
- Module visibility based on user role
- Navigation system working

---

### Task 8: Pinia Store Setup

**Objective**: Create centralized state management for authentication and UI

**Stores to Create:**
- `resources/js/Stores/auth.js` - User authentication state
- `resources/js/Stores/ui.js` - UI state (modals, notifications)

**Auth Store:**
```javascript
{
  user: null,          // Current user
  roles: [],          // User's roles
  permissions: [],    // User's permissions
  isAuthenticated: false,
  
  methods: {
    setUser(user),
    setRoles(roles),
    logout(),
    checkRole(role),
    hasPermission(permission)
  }
}
```

**UI Store:**
```javascript
{
  notifications: [],
  modals: {},
  loading: false,
  
  methods: {
    showNotification(message, type),
    closeNotification(id),
    openModal(modalId),
    closeModal(modalId),
    setLoading(status)
  }
}
```

**Deliverable:**
- Pinia stores configured
- Auth state persists across navigation
- Global notification system working
- Loading states manageable

---

### Task 9: Development Workflow & Git Setup

**Objective**: Establish development standards and Git workflow

**Files to Create:**
- `.env.example` - Template environment file
- `.gitignore` - Git ignore rules
- `README.md` - Project documentation
- `.editorconfig` - Code style consistency
- `composer.json` - PHP dependencies
- `package.json` - Node dependencies

**Git Setup:**
```bash
# Initialize repository
git init

# Create .gitignore
git add .gitignore
git commit -m "Initial commit: Setup .gitignore"

# Create feature branch for Phase 1
git checkout -b feature/phase-1-setup

# After completion, merge to main
git checkout main
git merge feature/phase-1-setup
git push origin main
```

**Development Standards:**
- PHP: Laravel Pint for code style
- JavaScript: ESLint for linting
- Vue: Follow Vue 3 Composition API best practices
- Commits: Follow Conventional Commits

**Deliverable:**
- Git repository initialized
- Proper .gitignore configured
- README with setup instructions
- Development workflow documented

---

### Task 10: Documentation

**Objective**: Document setup process and architecture decisions

**Documentation to Create:**
- `SETUP.md` - Local development setup guide
- `ARCHITECTURE.md` - System architecture overview
- `API_CONVENTIONS.md` - API development guidelines
- `CODE_STYLE.md` - Code style guidelines
- `DEPLOYMENT.md` - Deployment procedures

**Setup Guide Should Include:**
```markdown
1. Prerequisites (Docker, Git, etc.)
2. Clone repository
3. Environment setup
4. Database migration
5. Running development server
6. Common commands
7. Troubleshooting
```

**Deliverable:**
- Comprehensive documentation
- New developers can setup in <30 minutes
- Clear guidelines for code contributions

---

## Testing Strategy

### Unit Tests
- User model tests
- Role model tests
- Authentication logic tests

### Integration Tests
- Login flow
- RBAC system
- Database migrations

### Manual Testing
- Docker services start correctly
- Login page renders
- Authentication works
- Landing page shows modules
- Module visibility based on role

---

## Success Criteria

- ✅ Laravel 13 running in Docker
- ✅ MySQL 8.0 database running and seeded
- ✅ Vue 3 with Inertia.js rendering pages
- ✅ User authentication fully functional
- ✅ 7 roles created in database
- ✅ Role-based module visibility working
- ✅ Landing page displays correctly
- ✅ Pinia store managing auth state
- ✅ Development workflow documented
- ✅ Project pushed to Git repository
- ✅ `npm run dev` and `php artisan serve` both work
- ✅ Application accessible at http://localhost:8000

---

## Deliverables

1. **Codebase**
   - Laravel 13 project with all configurations
   - Vue 3 components and Inertia.js setup
   - Docker configuration files
   - Database migrations and seeders

2. **Documentation**
   - Setup guide
   - Architecture overview
   - API conventions
   - Code style guidelines

3. **Git Repository**
   - Initial commit with all Phase 1 code
   - .gitignore properly configured
   - README with clear instructions

4. **Working Environment**
   - Docker containers running
   - Application accessible locally
   - Database fully seeded
   - All systems tested and verified

---

## Commands Reference (Quick Start)

```bash
# Setup
docker-compose up -d --build

# Database
docker-compose exec app php artisan migrate --seed

# Development servers
# Terminal 1:
npm run dev

# Terminal 2:
docker-compose exec app php artisan serve

# Testing
docker-compose exec app php artisan test

# Code quality
docker-compose exec app ./vendor/bin/pint
docker-compose exec app ./vendor/bin/phpstan

# Access
# App: http://localhost:8000
# Vite: http://localhost:5173
# PhpMyAdmin: http://localhost:8080
```

---

## Next Phase

After Phase 1 completion, proceed to:
- **Phase 2**: Administrator Module (User & Role Management)

