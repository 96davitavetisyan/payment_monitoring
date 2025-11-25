# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Payment Monitoring is a Laravel 8 + Vue.js 2 SPA for unified management of contracts, clients, projects, and client feedback. It uses Laravel Sanctum for API authentication and Spatie Laravel Permission for comprehensive role-based access control.

## Technology Stack

- **Backend**: Laravel 8 (PHP 7.3|8.0+)
- **Frontend**: Vue.js 2.7 + Vue Router 3 + Bootstrap 5
- **Build Tool**: Laravel Mix with Webpack
- **Authentication**: Laravel Sanctum (token-based API auth)
- **Authorization**: Spatie Laravel Permission (roles & permissions)
- **Database**: MySQL/MariaDB (configured via .env, default port 3307)
- **Validation**: Laravel Form Request classes

## Development Commands

### Backend
```bash
# Install PHP dependencies
composer install

# Run migrations
php artisan migrate

# Seed database with roles, permissions, users, and test data
php artisan db:seed

# Clear configuration cache (important after config changes)
php artisan config:clear

# Run tests
vendor/bin/phpunit
# Or run specific test suite
vendor/bin/phpunit --testsuite=Feature
vendor/bin/phpunit --testsuite=Unit

# Start development server
php artisan serve
```

### Frontend
```bash
# Install JavaScript dependencies
npm install

# Development build with watch
npm run watch

# Production build
npm run production

# Hot module replacement (recommended for active development)
npm run hot
```

## Architecture

### Core Domain Models

The application revolves around seven main entities:

1. **Project** - Central entity representing customer projects
   - Fields: name, start_date, responsible_user_id, status (active/suspended)
   - Scope filtering: `forUser($userId)` returns projects for specific user
   - Initial projects: Nazar, Ari Ari, Tamias, Pay Ways, Nister
   - Location: `app/Models/Project.php`

2. **Company** - Customer companies managing subscriptions and transactions
   - Fields: name, product_id, contact_person, contact_email, contact_phone, is_active
   - Relationships: belongsTo Product, hasMany Transactions, hasMany CompanySubscriptions
   - Can be linked to transactions to track which company made payments
   - Sample companies: Tech Startup Inc, Digital Services LLC, Global Trade Corp, etc.
   - Location: `app/Models/Company.php`

3. **Product** - Subscription products offered to companies
   - Fields: name, description, monthly_price, is_active
   - Relationships: hasMany Companies
   - Sample products: Basic Plan ($29.99), Professional Plan ($79.99), Enterprise Plan ($199.99)
   - Location: `app/Models/Product.php`

4. **CompanySubscription** - Subscription records for companies
   - Fields: company_id, starts_from, price_per_month, status (active/stopped/cancelled)
   - Relationships: belongsTo Company
   - Tracks subscription lifecycle and pricing
   - Location: `app/Models/CompanySubscription.php`

5. **Transaction** - Comprehensive payment and contract records
   - Fields: project_id, company_id, company_name, person_name, transaction_date, max_overdue_date, amount, payment_status, transaction_type (one-time/monthly), contract dates, is_active
   - Relationships: belongsTo Project, belongsTo Company
   - Supports two file types: general attachments (file_path) and contracts (contract_file)
   - Has `is_active` flag for Active vs. History separation
   - Scopes: `active()` and `history()` for filtering
   - Files stored in `storage/app/transactions/` and `storage/app/contracts/`
   - Location: `app/Models/Transaction.php`

6. **Feedback** - Client relationship manager notes
   - Fields: project_id, account_manager_id, content, file_path
   - Links project to client relationship manager (User)
   - Supports file attachments stored in `storage/app/feedbacks/`
   - Location: `app/Models/Feedback.php`

7. **User** - Authentication + role-based authorization
   - Uses Spatie Permission traits for roles/permissions
   - Four roles: Super Administrator, Accountant, Office Manager, Client Relationship Manager
   - Location: `app/Models/User.php`

### Role-Based Permission Matrix

**Super Administrator:**
- Create, edit, view, delete projects
- Suspend/activate projects
- View transactions
- View feedback
- Full system access

**Accountant:**
- View all projects
- Suspend/activate projects
- View, edit, create, delete transactions (full Clients/Transactions table access)

**Office Manager:**
- View all projects
- View transactions
- Read-only access

**Client Relationship Manager:**
- View all projects
- View transactions
- Create, view, manage feedback
- Record client feedback with attachments

### Form Request Validation

All validation logic is encapsulated in dedicated Form Request classes (located in `app/Http/Requests/`):
- `StoreProjectRequest` / `UpdateProjectRequest`
- `StoreTransactionRequest` / `UpdateTransactionRequest`
- `StoreFeedbackRequest` / `UpdateFeedbackRequest`

Authorization checks are handled in the `authorize()` method of each request class using permission names with underscores (e.g., `create_projects`, `edit_transactions`, `manage_feedback`).

### API Architecture

All API routes are in `routes/api.php` under `/api` prefix with `auth:sanctum` middleware (except login).

**Authentication Flow:**
- Login: `POST /api/login` returns Sanctum token
- Store token in `localStorage` on frontend
- All subsequent requests include: `Authorization: Bearer {token}`
- Logout: `POST /api/logout` revokes token

**Resource Structure:**
```
/api/projects                                           # List/create projects
/api/projects/{project}                                 # Show/update/delete project
/api/projects/{project}/toggle-status                   # Toggle active/suspended
/api/projects/{project}/transactions                    # List/create transactions
/api/projects/{project}/transactions/{transaction}      # Update/delete transaction
/api/projects/{project}/transactions/{transaction}/toggle-status  # Move to history/active
/api/projects/{project}/feedbacks                       # List/create feedbacks
/api/projects/{project}/feedbacks/{feedback}            # Update/delete feedback

/api/companies                                          # List/create companies
/api/companies/{company}                                # Show/update/delete company
/api/companies/{company}/subscriptions                  # List company subscriptions

/api/products                                           # List/create products
/api/products/{product}                                 # Update/delete product
/api/products/{product}/companies                       # List companies using this product
```

### Frontend Architecture

**Single Page Application (SPA):**
- Entry point: `resources/views/welcome.blade.php` with `<div id="app">`
- All routes handled by Vue Router (history mode)
- Catch-all web route (`routes/web.php`) returns SPA entry point

**Component Organization:**
```
resources/js/
├── app.js                    # Vue app initialization, router setup
├── auth.js                   # Authentication utilities (permissions, roles)
├── bootstrap.js              # Axios configuration
└── components/
    ├── Auth/Login.vue        # Login form
    ├── Layout/Header.vue     # Navigation header with menu
    ├── Projects/             # Project management components
    ├── Transactions/         # Transaction management components
    ├── Feedbacks/            # Feedback management components
    ├── Companies/            # Companies management (CRUD)
    └── Products/             # Products and subscription management
```

**API Communication:**
- Axios configured globally in `resources/js/bootstrap.js`
- Base URL: `/` (same domain)
- Token automatically added to all requests from `localStorage`

## Key Implementation Patterns

### Transactions: Active vs. History
Transactions use `is_active` boolean to separate current transactions from historical records:
```php
$active = $project->transactions()->active()->get();
$history = $project->transactions()->history()->get();
```

Controllers provide `toggleStatus()` method to move transactions between active and history.

### File Uploads
Three types of file uploads are supported:
```php
// Transaction general files
if($request->hasFile('file')) {
    $data['file_path'] = $request->file('file')->store('transactions');
}

// Contract files
if($request->hasFile('contract_file')) {
    $data['contract_file'] = $request->file('contract_file')->store('contracts');
}

// Feedback files
if($request->hasFile('file')) {
    $data['file_path'] = $request->file('file')->store('feedbacks');
}
```

### Authorization Pattern
Form Requests handle authorization in `authorize()` method:
```php
public function authorize()
{
    return $this->user()->can('create_transactions');
}
```

Controllers additionally check permissions for operations not covered by Form Requests:
```php
if (!auth()->user()->can('view_all_projects')) {
    // Show only user's projects
}
```

### Route Model Binding
Laravel automatically resolves models from route parameters:
```php
Route::put('projects/{project}', [ProjectController::class, 'update']);
// Controller receives Project $project instance automatically
```

## Database Schema Notes

### Updated Tables

**projects:**
- name, start_date, responsible_user_id, status (active/suspended)

**transactions (updated):**
- project_id (foreign key to projects)
- company_id (NEW - foreign key to companies, nullable)
- company_name (renamed from customer_name)
- person_name (NEW)
- transaction_date
- max_overdue_date (NEW)
- amount
- payment_status (updated: paid, unpaid, late, overdue, notified)
- transaction_type (NEW: one-time/monthly)
- contract_start_date (NEW)
- contract_end_date (NEW)
- contract_file (NEW)
- is_active
- file_path

**products (NEW):**
- name
- description
- monthly_price
- is_active

**companies (NEW):**
- name
- product_id (foreign key to products)
- contact_person
- contact_email
- contact_phone
- is_active

**company_subscriptions (NEW):**
- company_id (foreign key to companies)
- starts_from
- price_per_month
- status (active/stopped/cancelled)

**feedbacks (updated):**
- project_id, account_manager_id, content
- file_path (NEW)

**Other tables:**
- `payments` - company_id, month (YYYY-MM), amount, paid_at, status
- `companies` + `company_subscriptions` - defined but no active models/controllers

### Permission Names Convention
All permissions use underscores: `create_projects`, `edit_transactions`, `manage_feedback`, `view_all_projects`, etc.

## Initial Data

The database seeders (`database/seeders/`) create:
- **4 Roles** with permission matrix as specified
- **4 Users** (one per role):
  - superadmin@example.com (Super Administrator)
  - accountant@example.com (Accountant)
  - officemanager@example.com (Office Manager)
  - clientmanager@example.com (Client Relationship Manager)
  - All passwords: `password`
- **5 Initial Projects**: Nazar, Ari Ari, Tamias, Pay Ways, Nister

## Testing

PHPUnit configured in `phpunit.xml`:
- Feature tests: `tests/Feature/`
- Unit tests: `tests/Unit/`
- Test environment uses array cache and sync queue
- Base test class: `tests/TestCase.php` with `CreatesApplication` trait
