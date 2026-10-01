# FSU Internal Finance System - Architecture Documentation

Technical architecture, design patterns, and system design for FSU Internal.

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        Client Browser                            │
└────────────────────────────────────────────────────────────────┬┘
                                                                   │
                        HTTP/WebSocket
                                                                   │
┌────────────────────────────────────────────────────────────────▼┐
│                    Laravel 13 (Port 8000)                        │
├────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  Inertia.js - Server-side rendering + Vue 3 rendering  │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │          HTTP Middleware Stack                          │   │
│  │  ├─ HandleInertiaRequests (Share auth data)            │   │
│  │  ├─ RoleMiddleware (Check user roles)                 │   │
│  │  ├─ PermissionMiddleware (Check permissions)          │   │
│  │  └─ CORS Middleware                                   │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │          Route Handlers (Controllers)                   │   │
│  │  ├─ AuthController (Login/Logout/Auth check)          │   │
│  │  ├─ DashboardController (Dashboard rendering)         │   │
│  │  └─ ModuleControllers (Future modules)                │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │          Business Logic & Models                        │   │
│  │  ├─ User Model (with role/permission relations)       │   │
│  │  ├─ Role Model (with permission relations)            │   │
│  │  ├─ Permission Model                                  │   │
│  │  └─ Policies (Authorization logic)                    │   │
│  └─────────────────────────────────────────────────────────┘   │
└────────────────────────────────────────────────────────────────┤
                                                                   │
             Database Connection & Session Storage
                                                                   │
┌──────────────────────┐  ┌──────────────────┐  ┌──────────────┐
│   MySQL 8.0          │  │  Redis 7.x       │  │ File System  │
│  (Port 3306)         │  │  (Port 6379)     │  │  (Storage)   │
│                      │  │                  │  │              │
│  ├─ users           │  │  ├─ Sessions     │  │  ├─ Logs     │
│  ├─ roles           │  │  ├─ Cache        │  │  ├─ Storage  │
│  ├─ permissions     │  │  └─ Jobs Queue   │  │  └─ Backups  │
│  └─ role_has_perm   │  │                  │  │              │
└──────────────────────┘  └──────────────────┘  └──────────────┘
```

## Frontend Architecture

### Vue 3 + Inertia.js Flow

```
┌─────────────────────────────────────────────────────┐
│  User Action (click, form submit)                  │
└──────────────────────────┬──────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────┐
│  Vue Component Event Handler                        │
│  (e.g., handleLogin, navigateToModule)             │
└──────────────────────────┬──────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────┐
│  Inertia.js Router                                  │
│  router.post('/login', formData)                   │
└──────────────────────────┬──────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────┐
│  HTTP Request → Laravel Backend                     │
│  POST /login with credentials                      │
└──────────────────────────┬──────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────┐
│  Laravel Middleware Pipeline                       │
│  ├─ Verify CSRF token                              │
│  ├─ Rate limit                                     │
│  └─ Handle errors                                  │
└──────────────────────────┬──────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────┐
│  Controller (AuthController@login)                  │
│  ├─ Validate input                                 │
│  ├─ Query database                                 │
│  └─ Return Inertia response                        │
└──────────────────────────┬──────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────┐
│  Inertia Server Response                           │
│  {                                                  │
│    "component": "Dashboard/Landing",               │
│    "props": {                                       │
│      "user": {...},                                │
│      "auth": {...}                                 │
│    }                                                │
│  }                                                  │
└──────────────────────────┬──────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────┐
│  Pinia Store Updates                               │
│  useAuthStore().setUser(userData)                  │
└──────────────────────────┬──────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────┐
│  Vue Component Re-renders                          │
│  (Dashboard/Landing.vue with user data)           │
└─────────────────────────────────────────────────────┘
```

### State Management (Pinia)

```
┌──────────────────────────────────────────────────┐
│         Pinia Store Architecture                 │
└──────────────────────────────────────────────────┘

useAuthStore()
├─ State
│  ├─ user: { id, name, email, role, status, ... }
│  ├─ isAuthenticated: boolean
│  ├─ loading: boolean
│  └─ error: string | null
│
├─ Computed
│  ├─ userRole() → role name
│  └─ userPermissions() → array of permissions
│
└─ Methods
   ├─ setUser(userData) → set user state
   ├─ clearUser() → logout
   ├─ hasRole(role) → boolean
   ├─ hasPermission(permission) → boolean
   └─ fetchCurrentUser() → async fetch from API

useUIStore()
├─ State
│  ├─ notifications: []
│  ├─ modals: { [name]: { isOpen, data } }
│  ├─ isLoading: boolean
│  └─ sidebarOpen: boolean
│
└─ Methods
   ├─ addNotification(notification)
   ├─ showSuccess(message)
   ├─ showError(message)
   ├─ openModal(name, data)
   ├─ closeModal(name)
   └─ toggleSidebar()
```

## Backend Architecture

### Authentication Flow

```
1. User submits login form
   ├─ POST /login { email, password }
   ├─ AuthController@login validates input
   └─ Query User model with email

2. Credentials validation
   ├─ Hash::check(password, user.password)
   ├─ Check user.status === 'active'
   └─ Create Sanctum token

3. Response
   ├─ Return user data + token
   ├─ Session created (cookie-based)
   └─ Redirect to dashboard

4. Subsequent requests
   ├─ Middleware checks auth status
   ├─ Sanctum validates token
   └─ Request proceeds or returns 401
```

### RBAC (Role-Based Access Control) Flow

```
1. User makes request to protected route
   ├─ GET /modules/collection
   └─ AuthController@show

2. RoleMiddleware checks
   ├─ User authenticated?
   ├─ User has required role?
   │  ├─ user.role()->where('name', 'COLLECTION_STAFF')
   │  └─ If yes → proceed
   │  └─ If no → abort(403)
   └─ Return response

3. PermissionMiddleware checks (more granular)
   ├─ User has specific permission?
   ├─ user.role.permissions()->where('name', 'view_collection')
   └─ If yes → proceed, If no → abort(403)

4. PolicyAuthorization (model-level)
   ├─ $this->authorize('view', $model)
   ├─ UserPolicy@view($user, $model)
   └─ Returns boolean for access
```

### Database Schema

```
┌──────────────────┐         ┌────────────────┐
│   users          │         │   roles        │
├──────────────────┤         ├────────────────┤
│ id (PK)         │────┐  ┌─│ id (PK)        │
│ role_id (FK)    │    │  │ │ name           │
│ name            │    │  │ │ description    │
│ email           │    │  │ │ created_at     │
│ password        │    │  │ │ updated_at     │
│ designation     │    │  │ └────────────────┘
│ rank            │    │  │
│ status (enum)   │    │  │  ┌────────────────┐
│ created_at      │    │  │  │ permissions    │
│ updated_at      │    │  │  ├────────────────┤
└──────────────────┘    │  └─│ id (PK)        │
                        │    │ name           │
                        │    │ description    │
                        │    │ created_at     │
                        │    │ updated_at     │
                        │    └────────────────┘
                        │
                        └──┐  ┌──────────────────────────┐
                           └─│ role_has_permissions     │
                              ├──────────────────────────┤
                              │ role_id (FK)            │
                              │ permission_id (FK)      │
                              │ (composite PK)          │
                              └──────────────────────────┘
```

## Security Architecture

### Authentication Security

- **Password Hashing**: bcrypt via Laravel Hash facade
- **Session Management**: HTTP-only cookies
- **CSRF Protection**: CSRF token in forms
- **Rate Limiting**: Built-in throttle middleware
- **Token Storage**: Sanctum tokens in database

### Authorization Security

- **Role-Based Access Control**: User → Role → Permissions
- **Permission Checking**: Both middleware and policy level
- **Database Constraints**: Foreign keys enforce data integrity
- **Middleware Pipeline**: Multiple checks before reaching handler

### Data Security

- **Environment Variables**: Sensitive data in .env (not in repo)
- **Encryption**: Laravel encryption for sensitive data
- **SQL Injection Prevention**: Eloquent ORM parameterized queries
- **XSS Prevention**: Vue.js escapes output automatically

## API Architecture

### Response Format

```javascript
// Inertia Response
{
  component: "Dashboard/Landing",  // Vue component to render
  props: {
    auth: {
      user: {
        id: 1,
        name: "Admin User",
        email: "admin@fsu.local",
        role: { id: 1, name: "ADMINISTRATOR" }
      }
    },
    modules: [...]  // Page-specific data
  },
  url: "/dashboard",
  version: "1.0"
}

// Error Response
{
  component: "Error",
  props: {
    status: 403,
    message: "Unauthorized"
  }
}
```

### Middleware Pipeline

```
Request
  ↓
├─ HandleCors (CORS headers)
├─ EncryptCookies (Encrypt request cookies)
├─ ShareErrorsFromSession (Share validation errors)
├─ StartSession (Start/resume session)
├─ VerifyCsrfToken (CSRF token verification)
├─ Authenticate (Check if authenticated)
├─ HandleInertiaRequests (Share auth data)
├─ RoleMiddleware (Check roles - if applied)
├─ PermissionMiddleware (Check permissions - if applied)
├─ ThrottleRequests (Rate limiting - if applied)
  ↓
Controller Handler
  ↓
Response
```

## File Organization

### Backend Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   └── ModuleControllers/ (future)
│   └── Middleware/
│       ├── HandleInertiaRequests.php
│       ├── RoleMiddleware.php
│       └── PermissionMiddleware.php
├── Models/
│   ├── User.php
│   ├── Role.php
│   └── Permission.php
└── Policies/
    └── UserPolicy.php

database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 2026_10_01_155424_create_roles_table.php
│   ├── 2026_10_01_155425_create_permissions_table.php
│   └── 2026_10_01_155426_create_role_has_permissions_table.php
└── seeders/
    ├── RoleSeeder.php
    ├── PermissionSeeder.php
    ├── UserSeeder.php
    └── DatabaseSeeder.php

routes/
└── web.php
```

### Frontend Structure
```
resources/js/
├── Pages/
│   ├── Welcome.vue (landing page)
│   ├── Auth/
│   │   └── Login.vue
│   ├── Dashboard/
│   │   └── Landing.vue
│   └── Modules/
│       ├── Collection.vue
│       ├── Finance.vue
│       ├── Disbursement.vue
│       └── Reports.vue
├── Components/
│   ├── Layout/
│   │   └── AppLayout.vue
│   └── Common/
│       └── NotificationCenter.vue
├── Stores/
│   ├── auth.js (Pinia)
│   └── ui.js (Pinia)
├── Composables/
│   └── useAppStore.js
├── app.js (entry point)
└── bootstrap.js
```

## Performance Considerations

### Database Optimization
- **Eager Loading**: Use `with()` to prevent N+1 queries
- **Indexing**: Foreign keys and frequently queried columns indexed
- **Query Scopes**: Reusable query filters
- **Pagination**: Limit result sets

### Frontend Optimization
- **Code Splitting**: Vite automatically splits modules
- **Lazy Loading**: Vue 3 supports route-based splitting
- **Asset Caching**: Browser caching with cache-busting
- **Minification**: Vite minifies CSS and JS in production

### Caching Strategy
- **Page Cache**: Cache full pages for anonymous users
- **Query Cache**: Redis for frequent database queries
- **Session Cache**: Redis for session storage
- **Asset Cache**: Browser cache with version hashing

## Deployment Architecture

### Development
```
localhost:8000 (Laravel)
localhost:5173 (Vite)
localhost:3306 (MySQL)
localhost:6379 (Redis)
localhost:8080 (PHPMyAdmin)
```

### Production (Laravel Cloud)
```
Production DB:    MySQL managed database
Production Cache: Redis cluster
Frontend:         Vite-built static files
API:             Laravel 13 deployed
SSL/TLS:         Automatic with Laravel Cloud
```

## Monitoring & Logging

### Laravel Logging
- Location: `storage/logs/laravel.log`
- Format: Structured with level, message, stack trace
- Rotation: Daily rotation configured

### Application Monitoring
- Database queries logged
- Authentication events logged
- Authorization failures logged
- User actions (future audit logging)

## Future Enhancements

- **API Rate Limiting**: Per-user rate limits
- **Audit Logging**: Complete audit trail of changes
- **Two-Factor Authentication**: Enhanced security
- **Advanced Permissions**: Attribute-based access control
- **API Documentation**: OpenAPI/Swagger
- **Testing**: Unit and feature test suite
- **CI/CD Pipeline**: Automated testing and deployment

---

**Last Updated**: October 1, 2026
**Version**: Phase 1 Foundation
