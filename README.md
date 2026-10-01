# FSU Internal Finance System

A modern, secure financial management system built for the Federal Savings Union. Designed with role-based access control, comprehensive audit trails, and modular architecture for scalability.

**Status**: Phase 1 Foundation Complete ✅ | 9/10 Tasks Done

## 🎯 Overview

FSU Internal is a Laravel 13 monolithic backend with Vue 3 + Inertia.js frontend, providing:

- **Authentication**: Secure login with Laravel Sanctum
- **Authorization**: Granular role-based access control (RBAC) with 7 roles and 19 permissions
- **Modules**: Collection, Finance, Disbursement, Reports
- **State Management**: Pinia stores for auth and UI
- **Styling**: Tailwind CSS for modern, responsive design
- **Infrastructure**: Docker containerization with MySQL, Redis, Node services

## 📋 Tech Stack

| Component | Technology | Version |
|-----------|-----------|---------|
| **Backend** | Laravel | 13.34.0 |
| **Frontend** | Vue 3 + Inertia.js | 3.x |
| **Styling** | Tailwind CSS | 4.x |
| **State** | Pinia | 2.x |
| **Auth** | Laravel Sanctum | - |
| **Database** | MySQL | 8.0 |
| **Cache** | Redis | 7.x |
| **Infrastructure** | Docker | Compose |

## 🚀 Quick Start

### Prerequisites

- PHP 8.5+ and Composer
- Node.js 18+ and npm
- Docker and Docker Compose
- MySQL 8.0 (via Docker)

### Installation

1. **Clone and Setup**
   ```bash
   cd /home/julius/projects/fsu-internal
   composer install
   npm install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Start Docker Services**
   ```bash
   docker compose up -d
   # Wait 10 seconds for MySQL to be ready
   sleep 10
   ```

4. **Database Setup**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Build Frontend**
   ```bash
   npm run build
   ```

6. **Start Development Servers**
   ```bash
   # Terminal 1: Laravel dev server
   php artisan serve
   
   # Terminal 2: Vite development server
   npm run dev
   ```

7. **Access Application**
   - Frontend: http://localhost:8000
   - PHPMyAdmin: http://localhost:8080

### Test Accounts

All accounts use password: `password123`

| Email | Role | Permissions |
|-------|------|-------------|
| admin@fsu.local | ADMINISTRATOR | All permissions |
| collector@fsu.local | COLLECTION_STAFF | Collection, Reports |
| finance@fsu.local | FINANCE_STAFF | Finance, Reports |
| viewer@fsu.local | VIEWER | View-only access |

## 📁 Project Structure

```
fsu-internal/
├── .kiro/                          # Kiro IDE configuration
│   ├── specs/                      # Project specifications
│   └── steering/                   # Development guidelines
├── app/
│   ├── Http/
│   │   ├── Controllers/            # Request handlers (Auth, Dashboard)
│   │   └── Middleware/             # Auth, roles, permissions
│   └── Models/                     # Eloquent models (User, Role, Permission)
├── database/
│   ├── migrations/                 # Schema migrations
│   └── seeders/                    # Database seeders
├── resources/
│   ├── js/
│   │   ├── Stores/                 # Pinia stores (auth, ui)
│   │   ├── Composables/            # Vue composables
│   │   ├── Components/             # Reusable Vue components
│   │   └── Pages/                  # Page components (Auth, Dashboard, Modules)
│   └── css/                        # Tailwind CSS
├── routes/
│   └── web.php                     # Web routes with Inertia
├── docker-compose.yml              # Docker services
├── vite.config.js                  # Vite build config
└── tailwind.config.js              # Tailwind configuration
```

## 🔐 Authentication & Authorization

### Roles (7 Total)

| Role | Purpose | Modules |
|------|---------|---------|
| ALL_ACCESS | Full system access | All |
| ADMINISTRATOR | System admin | All |
| CO_ADMIN | Co-administrator | Most modules |
| COLLECTION_STAFF | Collection operations | Collection, Reports |
| FINANCE_STAFF | Financial operations | Finance, Reports |
| DISBURSEMENT_OFFICER | Disbursement approvals | Disbursement, Reports |
| VIEWER | Read-only access | View permissions only |

### Permissions (19 Total)

**Collection Module** (4)
- create_collection, view_collection, edit_collection, delete_collection

**Finance Module** (4)
- create_finance, view_finance, edit_finance, delete_finance

**Disbursement Module** (4)
- create_disbursement, view_disbursement, approve_disbursement, delete_disbursement

**Reports Module** (3)
- view_reports, generate_reports, export_reports

**Admin Module** (4)
- manage_users, manage_roles, manage_settings, view_audit_log

## 🗄️ Database Schema

### Core Tables

**users**
- id, role_id, name, designation, rank, status, email, password, timestamps

**roles**
- id, name, description, timestamps

**permissions**
- id, name, description, timestamps

**role_has_permissions**
- role_id, permission_id (pivot table)

**personal_access_tokens**
- Sanctum tokens for API authentication

## 🎨 Frontend Architecture

### Pages (Location: `resources/js/Pages/`)
- **Welcome.vue** - Landing page
- **Auth/Login.vue** - Login form with Sanctum integration
- **Dashboard/Landing.vue** - Dashboard with module access
- **Modules/** - Collection, Finance, Disbursement, Reports

### Components (Location: `resources/js/Components/`)
- **Layout/AppLayout.vue** - Main layout with navigation
- **Common/NotificationCenter.vue** - Toast notifications

### State Management (Pinia Stores)

**useAuthStore** (`resources/js/Stores/auth.js`)
```javascript
import { useAuthStore } from '@/Stores/auth'
const auth = useAuthStore()

// Methods
auth.setUser(userData)
auth.hasRole('ADMINISTRATOR')
auth.hasPermission('create_collection')
auth.fetchCurrentUser()
```

**useUIStore** (`resources/js/Stores/ui.js`)
```javascript
import { useUIStore } from '@/Stores/ui'
const ui = useUIStore()

// Methods
ui.showSuccess('Operation successful')
ui.showError('An error occurred')
ui.openModal('confirmDelete', { id: 123 })
```

### Composables

**useAppStore** - Access both auth and ui stores
**useAuth** - Shorthand for auth store
**useUI** - Shorthand for ui store

## 🐳 Docker Services

```bash
# View running services
docker compose ps

# View logs
docker compose logs -f [service-name]

# Access MySQL
docker compose exec mysql mysql -u root -p

# Restart services
docker compose restart
```

**Services**:
- **fsu-app**: PHP 8.5 + Laravel
- **fsu-mysql**: MySQL 8.0 database
- **fsu-redis**: Redis cache
- **fsu-node**: Node.js for build tools
- **fsu-phpmyadmin**: Database management UI

## 📚 API Endpoints

### Authentication
- `POST /login` - User login (returns token)
- `POST /logout` - User logout
- `GET /me` - Get current user

### Dashboard
- `GET /dashboard` - Dashboard landing page
- `GET /modules/collection` - Collection module
- `GET /modules/finance` - Finance module
- `GET /modules/disbursement` - Disbursement module
- `GET /modules/reports` - Reports module

## 🛠️ Development Commands

### Backend
```bash
# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Create new controller
php artisan make:controller ControllerName

# Create new model
php artisan make:model ModelName
```

### Frontend
```bash
# Development server with hot reload
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Lint code
npm run lint
```

### Docker
```bash
# Start services
docker compose up -d

# Stop services
docker compose down

# Rebuild services
docker compose build --no-cache
```

## 🧪 Testing

### Run Tests
```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/AuthTest.php

# With coverage
php artisan test --coverage
```

## 📝 Phase 1 Tasks Completed

- ✅ Task 1: Laravel 13 Project Initialization
- ✅ Task 2: Inertia.js + Vue 3 Setup
- ✅ Task 3: Docker Environment Setup
- ✅ Task 4: Database Schema & Migrations
- ✅ Task 5: Authentication System (Sanctum)
- ✅ Task 6: Role-Based Access Control (RBAC)
- ✅ Task 7: Landing Page & Module Access UI
- ✅ Task 8: Pinia Store Setup
- ✅ Task 9: Development Workflow & Git Setup
- ✅ Task 10: Documentation

## 🔄 Phase 2 Roadmap

- Admin Module (User Management, Role Management, Settings)
- Collection Module (Transaction Entry, Validation, Reports)
- Finance Module (Record Management, Reconciliation)
- Disbursement Module (Request Management, Approval Workflow)
- Reports Module (Advanced Reporting, Export Functionality)
- Audit Logging & Compliance

## 📖 Documentation Files

- **README.md** - This file
- **SETUP.md** - Detailed setup instructions
- **ARCHITECTURE.md** - Technical architecture details
- **.kiro/specs/phase-1-foundation-setup.md** - Spec document with all tasks
- **.kiro/steering/git-workflow.md** - Git workflow guidelines

## 🤝 Contributing

1. Create a feature branch: `git checkout -b feature/your-feature`
2. Commit changes: `git commit -m "feat(scope): description"`
3. Push branch: `git push origin feature/your-feature`
4. Follow guidelines in `.kiro/steering/git-workflow.md`

## 📞 Support

For issues or questions:
1. Check documentation in `docs/` folder
2. Review `.kiro/specs/` for technical specifications
3. Consult `.kiro/steering/` for development guidelines

## 📄 License

Internal FSU project - Confidential

---

**Last Updated**: October 1, 2026
**Current Version**: Phase 1 Foundation
**Lead Developer**: FSU Development Team
