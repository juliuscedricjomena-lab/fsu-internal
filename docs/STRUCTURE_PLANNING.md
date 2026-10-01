# FSU Internal Finance System - Structure Planning

## System Overview

A role-based Finance Management System for Finance Service Unit 18 at Philippine National Police Academy with 5 main modules: Administrator, Collection, Pay & Allowances, Remittance, and Disbursement.

---

## Technology Stack Recommendations

### Backend
- **Framework**: Node.js + Express / Django / FastAPI
- **Database**: PostgreSQL (relational data) + Redis (caching)
- **Authentication**: JWT tokens with role-based access control (RBAC)
- **File Upload**: Multer (Node.js) / Python file handling
- **PDF Generation**: PDFKit / ReportLab

### Frontend
- **Framework**: React / Vue.js
- **State Management**: Redux / Vuex
- **UI Library**: Bootstrap / Material-UI / Tailwind CSS
- **Charts/Reports**: Chart.js / ECharts

### Infrastructure
- **Deployment**: Docker + Kubernetes / AWS / Azure
- **Version Control**: Git
- **CI/CD**: GitHub Actions / GitLab CI

---

## Database Schema Outline

### Core Tables

#### 1. Users
```
- user_id (PK)
- full_name
- designation
- rank
- email
- password_hash
- role_id (FK)
- status (active/inactive)
- created_at
- updated_at
```

#### 2. Roles
```
- role_id (PK)
- role_name (ADMINISTRATOR, CO-ADMIN, VIEWER, ALL_ACCESS)
- description
```

#### 3. Module_Access
```
- access_id (PK)
- role_id (FK)
- module_id (FK)
- can_view (boolean)
- can_create (boolean)
- can_edit (boolean)
- can_delete (boolean)
```

#### 4. Modules
```
- module_id (PK)
- module_name (Administrator, Collection, Pay_Allowances, Remittance, Disbursement)
- description
```

#### 5. Collection_Transactions
```
- transaction_id (PK)
- account_type (General Fund, Overpayment, Beyond Economic Repair, etc.)
- payor_name
- transaction_date
- official_receipt_number
- official_receipt_file_path
- amount
- created_by (FK: users)
- created_at
- updated_at
```

#### 6. Remittance_Uploads
```
- remittance_id (PK)
- remittance_type (PAG-IBIG, PHILHEALTH)
- file_path
- file_name
- upload_date
- uploaded_by (FK: users)
- status
```

#### 7. Payslips
```
- payslip_id (PK)
- employee_id (FK: users)
- month
- year
- total_salary
- days_worked
- days_absent
- computed_salary
- status (PAYSLIP, READMITTED, TURNBACK)
- file_path
- created_at
```

#### 8. Disbursement_Records
```
- disbursement_id (PK)
- disbursement_type (Personnel Services, MOOE, Capital Outlay, etc.)
- amount
- purpose
- date
- approved_by (FK: users)
- created_by (FK: users)
- status
```

#### 9. Reports
```
- report_id (PK)
- report_type (Daily, Monthly, Custom Date Range)
- module_type (Collection, Disbursement, etc.)
- generated_date
- generated_by (FK: users)
- file_path
- filter_criteria (JSON)
```

---

## API Endpoints Structure

### Authentication
```
POST   /api/auth/login
POST   /api/auth/logout
POST   /api/auth/refresh-token
GET    /api/auth/profile
```

### Administrator Module
```
GET    /api/admin/users
POST   /api/admin/users
PUT    /api/admin/users/:id
DELETE /api/admin/users/:id
GET    /api/admin/roles
POST   /api/admin/roles
PUT    /api/admin/roles/:id
GET    /api/admin/access-restrictions
PUT    /api/admin/access-restrictions/:id
```

### Collection Module
```
POST   /api/collection/transactions
GET    /api/collection/transactions
GET    /api/collection/transactions/:id
PUT    /api/collection/transactions/:id
DELETE /api/collection/transactions/:id
GET    /api/collection/reports/daily
GET    /api/collection/reports/monthly
GET    /api/collection/reports/by-date-range
```

### Pay & Allowances Module
```
POST   /api/payroll/payslips/upload
GET    /api/payroll/payslips
GET    /api/payroll/payslips/:id
PUT    /api/payroll/payslips/:id
GET    /api/payroll/payslips/download/:id
POST   /api/payroll/payslips/compute
```

### Remittance Module
```
POST   /api/remittance/upload
GET    /api/remittance/files
GET    /api/remittance/files/:id
DELETE /api/remittance/files/:id
GET    /api/remittance/files/by-date-range
GET    /api/remittance/files/type/:type
```

### Disbursement Module
```
POST   /api/disbursement/records
GET    /api/disbursement/records
GET    /api/disbursement/records/:id
PUT    /api/disbursement/records/:id
DELETE /api/disbursement/records/:id
GET    /api/disbursement/by-type/:type
```

---

## Project Directory Structure

```
fsu-internal/
├── backend/
│   ├── src/
│   │   ├── config/
│   │   │   ├── database.js
│   │   │   ├── auth.js
│   │   │   └── constants.js
│   │   ├── controllers/
│   │   │   ├── auth.controller.js
│   │   │   ├── admin.controller.js
│   │   │   ├── collection.controller.js
│   │   │   ├── payroll.controller.js
│   │   │   ├── remittance.controller.js
│   │   │   └── disbursement.controller.js
│   │   ├── routes/
│   │   │   ├── auth.routes.js
│   │   │   ├── admin.routes.js
│   │   │   ├── collection.routes.js
│   │   │   ├── payroll.routes.js
│   │   │   ├── remittance.routes.js
│   │   │   └── disbursement.routes.js
│   │   ├── middleware/
│   │   │   ├── auth.middleware.js
│   │   │   ├── rbac.middleware.js
│   │   │   └── error.middleware.js
│   │   ├── models/
│   │   │   ├── User.model.js
│   │   │   ├── Role.model.js
│   │   │   ├── Transaction.model.js
│   │   │   ├── Payslip.model.js
│   │   │   ├── Remittance.model.js
│   │   │   └── Disbursement.model.js
│   │   ├── services/
│   │   │   ├── auth.service.js
│   │   │   ├── user.service.js
│   │   │   ├── collection.service.js
│   │   │   ├── payroll.service.js
│   │   │   ├── remittance.service.js
│   │   │   └── report.service.js
│   │   ├── utils/
│   │   │   ├── fileHandler.js
│   │   │   ├── pdfGenerator.js
│   │   │   ├── validators.js
│   │   │   └── helpers.js
│   │   └── app.js
│   ├── tests/
│   ├── .env
│   ├── .env.example
│   ├── package.json
│   └── server.js
│
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   │   ├── Auth/
│   │   │   │   ├── Login.jsx
│   │   │   │   └── Logout.jsx
│   │   │   ├── Layout/
│   │   │   │   ├── Header.jsx
│   │   │   │   ├── Sidebar.jsx
│   │   │   │   └── Layout.jsx
│   │   │   ├── Administrator/
│   │   │   │   ├── UserManagement.jsx
│   │   │   │   ├── RoleManagement.jsx
│   │   │   │   └── AccessControl.jsx
│   │   │   ├── Collection/
│   │   │   │   ├── TransactionEntry.jsx
│   │   │   │   ├── TransactionList.jsx
│   │   │   │   └── CollectionReports.jsx
│   │   │   ├── PayAllowances/
│   │   │   │   ├── PayslipUpload.jsx
│   │   │   │   ├── PayslipList.jsx
│   │   │   │   └── PayslipComputation.jsx
│   │   │   ├── Remittance/
│   │   │   │   ├── FileUpload.jsx
│   │   │   │   └── FileList.jsx
│   │   │   └── Disbursement/
│   │   │       ├── DisbursementForm.jsx
│   │   │       └── DisbursementList.jsx
│   │   ├── pages/
│   │   │   ├── Dashboard.jsx
│   │   │   ├── LandingPage.jsx
│   │   │   ├── AdminPanel.jsx
│   │   │   └── NotFound.jsx
│   │   ├── store/
│   │   │   ├── actions/
│   │   │   ├── reducers/
│   │   │   └── index.js
│   │   ├── services/
│   │   │   ├── api.js
│   │   │   ├── auth.service.js
│   │   │   └── modules.service.js
│   │   ├── utils/
│   │   │   ├── constants.js
│   │   │   └── helpers.js
│   │   ├── App.jsx
│   │   └── index.js
│   ├── public/
│   ├── .env
│   ├── .env.example
│   ├── package.json
│   └── vite.config.js
│
├── database/
│   ├── migrations/
│   ├── seeds/
│   └── schema.sql
│
├── docs/
│   ├── API_Documentation.md
│   ├── Database_Schema.md
│   ├── User_Guide.md
│   └── Deployment_Guide.md
│
├── docker-compose.yml
├── Dockerfile
├── .gitignore
├── README.md
└── FSU_Internal_Presentation_Details.md
```

---

## User Flow Diagram

```
1. User Login
   ↓
2. Authentication Check
   ↓
3. Display "I UNDERSTAND" Landing Page
   ↓
4. User Clicks "I UNDERSTAND"
   ↓
5. Role-Based Module Access
   ├─→ If ADMINISTRATOR: Show all modules
   ├─→ If CO-ADMIN: Show limited modules
   ├─→ If Collection Staff: Show only Collection
   ├─→ If Finance Staff: Show Payroll + Remittance
   └─→ If Disbursement Officer: Show only Disbursement
   ↓
6. User Navigates to Selected Module
   ↓
7. Module-Specific Interface Loads
```

---

## Module Workflow Details

### Collection Module Workflow
```
1. User navigates to Collection
2. Selects TRANSACTIONS or REPORTS
3. If TRANSACTIONS:
   - Select Account Type (General Fund, Overpayment, etc.)
   - Enter Payor Name
   - Select Transaction Date
   - Enter Official Receipt Number
   - Upload Receipt Attachment
   - Enter Amount
   - Submit
4. If REPORTS:
   - Select Report Type (Daily/Monthly/Date-Range)
   - System generates sorted reports
   - Prepare Report with timestamp
```

### Pay & Allowances Workflow
```
1. User uploads payslip file (monthly, PDF format)
2. System filters by month
3. For each entry:
   - Calculate: Total Salary - Days Absent
   - Status: PAYSLIP / READMITTED / TURNBACK
4. Display computed payslips
5. User can review and download
```

### Remittance Workflow
```
1. User selects remittance type (PAG-IBIG / PHILHEALTH)
2. Uploads file
3. System displays:
   - Successfully uploaded confirmation
   - File list sorted ascending by date
   - Date filtering available
```

---

## Security Considerations

1. **Authentication**: JWT tokens with expiration
2. **Authorization**: Role-Based Access Control (RBAC)
3. **Data Encryption**: Encrypt sensitive data at rest and in transit
4. **File Upload Validation**: Validate file type and size
5. **SQL Injection Prevention**: Use parameterized queries
6. **CSRF Protection**: Implement CSRF tokens
7. **Audit Logging**: Log all user actions
8. **Rate Limiting**: Prevent brute force attacks

---

## Implementation Phases

### Phase 1: Setup & Core Infrastructure
- Set up project structure
- Configure database
- Implement authentication system
- Set up basic RBAC

### Phase 2: Administrator Module
- User management
- Role management
- Access control configuration

### Phase 3: Collection Module
- Transaction entry form
- Report generation
- File upload

### Phase 4: Pay & Allowances Module
- Payslip upload
- Computation logic
- PDF storage

### Phase 5: Remittance Module
- File upload interface
- Date filtering
- File management

### Phase 6: Disbursement Module
- Disbursement form
- Budget tracking
- Category management

### Phase 7: Testing & Deployment
- Unit testing
- Integration testing
- User acceptance testing
- Deployment to production

---

## Success Criteria

- [ ] All users can log in with correct role assignment
- [ ] Module access properly restricted by role
- [ ] Collection transactions can be entered and retrieved
- [ ] Payslips computed correctly based on days worked
- [ ] Remittance files uploaded and filtered correctly
- [ ] Reports generated in requested formats
- [ ] File uploads stored securely
- [ ] Audit logs track all user actions
- [ ] System handles 100+ concurrent users
- [ ] <2 second response time for most operations

