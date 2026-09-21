# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Setup
```bash
# Install dependencies
composer install
npm install

# Copy environment file and generate key
cp .env.example .env
php artisan key:generate

# Create database and run migrations
touch database/database.sqlite
php artisan migrate

# Optional: Seed with sample data
php artisan db:seed
```

### Development Server
```bash
# Start all services concurrently (recommended)
composer run dev
# This runs: Laravel dev server, queue worker, logs viewer, and Vite

# Or start individually:
php artisan serve              # HTTP server (http://localhost:8000)
php artisan queue:listen       # Queue processor (database driver)
php artisan pail               # Log viewer
npm run dev                    # Vite dev server (Hot Module Replacement)
```

### Testing
```bash
# Run all tests
composer test

# Run specific test suite
php artisan test --filter=Feature
php artisan test --filter=Unit

# Run single test
php artisan test tests/Feature/ExampleTest.php

# Run tests with coverage
php artisan test --coverage

# Run a specific test method
php artisan test --filter=test_method_name
```

### Code Quality
```bash
# Run Laravel Pint (code formatter)
composer run format

# Run static analysis
composer run analyse

# Generate IDE helper for better autocomplete
php artisan ide-helper:generate
```

### Database
```bash
# Reset and re-seed database
php artisan migrate:fresh --seed

# Create migration
php artisan make:migration create_table_name

# Create model
php artisan make:model ModelName

# Create seeder
php artisan make:seeder SeederName

# Create factory
php artisan make:factory FactoryName --model=ModelName

# Refresh database with seeders
php artisan migrate:refresh --seed
```

### Queue & Workers
```bash
# Start queue worker (database connection)
php artisan queue:work --tries=3

# Start queue worker with timeout
php artisan queue:work --timeout=90

# Process failed jobs
php artisan queue:retry all
php artisan queue:flush

# Monitor failed jobs
php artisan queue:failed
```

### Cache
```bash
# Clear application cache
php artisan cache:clear

# Clear route cache
php artisan route:clear

# Clear config cache
php artisan config:clear

# Clear view cache
php artisan view:clear

# Clear all caches
php artisan optimize:clear
```

## Code Architecture

### Laravel Structure
This is a Laravel 13 application implementing a community management system (Subsync) with multi-role authentication:

- **App/Http/Controllers**: HTTP controllers handling requests (separated by role where appropriate)
- **App/Models**: Eloquent models representing database tables
- **Routes/web.php**: Web routes defining application endpoints
- **Resources/Views**: Blade templates for rendering HTML
- **Database/Migrations**: Database schema version control
- **Database/Seeders**: Sample data for development/testing
- **App/Http/Middleware**: Request filtering and authentication guards
- **config/auth.php**: Custom authentication guards configuration
- **resources/views/layouts/**: Shared Blade layouts for different user types

### Authentication System
Custom multi-guard authentication system with three distinct user types:

1. **Admin** (`admin` guard): Full system access
   - Model: `App\Models\Admin`
   - Login route: `/admin/login`
   - Middleware: `admin.auth`
   - Dashboard: `/dashboard`

2. **Officer** (`officer` guard): Community management staff
   - Model: `App\Models\Officer`
   - Login route: `/login` (with role=officer)
   - Middleware: `officer.auth`
   - Portal: `/officer-portal`

3. **Resident** (`resident` guard): Community members
   - Model: `App\Models\Resident`
   - Login route: `/login` (default role=resident)
   - Middleware: `auth:resident`
   - Portal: `/residents`

Authentication logic resides in `App\Http\Controllers\AuthController.php` with separate methods for each user type login.

### Key Features Implemented

1. **Authentication System**: Custom guards for admins, officers, and residents with approval workflow
2. **Announcements**: CRUD functionality with archiving and view tracking
3. **Financial Management**: Tracking dues, payments, and financial reports
4. **Issue Tracking**: Reporting and managing community issues with categorization
5. **Messaging**: Real-time communication between users (residents ↔ admins/officers)
6. **File Management**: Document storage for officers and residents
7. **Dashboard**: Role-specific dashboards with consolidated data
8. **Mapping**: Geographic visualization of households and facilities using Leaflet
9. **Recommendations**: System for residents to suggest improvements
10. **Household Management**: Family and resident relationship tracking
11. **HOA Expenses Transparency**: Residents can view how HOA fees are being utilized
12. **Alert System**: Notification system for admins, officers, and residents
13. **Report Generation**: Template-based report generation for residents
14. **Event Broadcasting**: Real-time updates for HOA expenses via WebSocket

### Common Patterns

- **Authentication**: Custom middleware (`AdminAuth.php`, `OfficerAuth.php`, `PortalRole.php`) for role-based access control
- **Validation**: Form requests used in controllers for input validation (check controller methods)
- **Eloquent Relationships**: Extensive model relationships (see models for `hasMany`, `belongsTo`, `hasManyThrough`)
- **Resource Controllers**: Standard RESTful controllers for most entities
- **Blade Templating**: Layout-based views with shared layouts in `resources/views/layouts/`
- **API Design**: JSON API endpoints under `/api` prefix for frontend consumption
- **Database Transactions**: Used in seeders and critical operations
- **Event Broadcasting**: Real-time features using Laravel events and broadcasting
- **Template System**: Report templates with variable substitution for dynamic content generation

### Environment
- Uses SQLite for development (`database/database.sqlite`)
- Environment variables managed through `.env` file (see `.env.example`)
- Queue system configured for database jobs (`QUEUE_CONNECTION=database`)
- Broadcasting configured for real-time features (currently using log driver, configurable to Pusher/Redis)
- Filesystem storage: local (public/storage)
- Cache: database driver (`CACHE_STORE=database`)
- Session: database driver (`SESSION_DRIVER=database`)

### API Design
The application follows a RESTful API design pattern:
- API routes are prefixed with `/api`
- Resource controllers handle standard CRUD operations
- Custom endpoints for specific functionality (e.g., `/api/dashboard-data` combines multiple queries)
- Response format is JSON with consistent structure
- Authentication via Laravel Sanctum or session guards depending on client

### Performance Considerations
- The dashboard endpoint (`/api/dashboard-data`) combines 11+ parallel calls into a single endpoint
- Eloquent eager loading used to prevent N+1 query problems
- Selective column selection minimizes data transfer
- Database sessions and caching for improved performance
- Queue system for background processing (notifications, reports, etc.)

### Frontend Assets
- Built with Vite and Tailwind CSS
- Development: `npm run dev` (Hot Module Replacement)
- Production: `npm run build`
- Asset compilation managed via `vite.config.js` and Laravel Vite Plugin

## Testing Conventions
- Feature tests in `tests/Feature/` directory (HTTP endpoint testing)
- Unit tests in `tests/Unit/` directory (isolated class/method testing)
- Uses Laravel's built-in testing utilities with PHPUnit
- Tests database transactions with `RefreshDatabase` trait where needed
- Mocking facilities available via Mockery and Laravel's Bus::fake(), Storage::fake(), etc.

## Important Files to Review
- `routes/web.php` - Understand application routes and controller mappings
- `app/Http/Controllers/` - Review controller logic for business rules and validation
- `app/Models/` - Understand data structure, relationships, and mutators/casts
- `database/migrations/` - Review database schema evolution and indexing
- `database/seeders/` - See sample data structure and relationships
- `resources/views/layouts/` - Shared Blade templates and layout structure
- `app/Http/Middleware/` - Custom authentication guard implementations
- `config/auth.php` - Authentication guard and provider configuration
- `app/Events/` and `app/Listeners/` - Event-driven architecture (if applicable)
- `app/Jobs/` - Queued jobs for background processing

## Laravel Boost
This repository has Laravel Boost installed for AI-assisted development:
- Provides 15+ tools and skills for agents like Claude Code
- Installed via `composer require laravel/boost --dev`
- Configured with `php artisan boost:install`
- Enables AI-powered code generation, analysis, and debugging capabilities

## Troubleshooting
- **Login issues**: Check that users have correct status ('Active' not 'Pending') in seeders
- **Migration errors**: Ensure `database/database.sqlite` exists and is writable
- **Queue not processing**: Verify `QUEUE_CONNECTION=database` in .env and run `php artisan queue:work`
- **Assets not updating**: Clear Vite cache with `rm -rf node_modules/.vite` or check vite.config.js
- **Authentication guard errors**: Verify guards are properly defined in config/auth.php
- **CSRF token mismatch**: Ensure web routes use web middleware group or exclude from CSRF when needed for API
- **Broadcasting issues**: Check BROADCAST_DRIVER in .env (currently set to log, can be changed to pusher/redis)
- **Real-time features**: Ensure WebSocket server is running if using Pusher/Redis for broadcasting

## Specific Development Tips for Subsync

### Working with Multi-Guard Authentication
When developing features that need to check authentication for specific roles:
- Use middleware: `portal.role:admin,officer,resident` for API routes
- Check session data: `session('active_role')` to determine current user type
- Access authenticated user: `Auth::guard('resident')->user()` etc.

### Optimized Data Loading
The `/api/dashboard-data` endpoint demonstrates optimized data fetching:
- Uses eager loading (`with()`) to prevent N+1 queries
- Selects only needed columns with `select()`
- Maps collections to minimize data transfer
- Combines multiple related queries into single endpoint

### File Storage Patterns
Officer and resident file management follows this pattern:
- Files stored in `storage/app/public/{category}`
- Use Laravel's Storage facade with `Storage::disk('public')`
- Files are linked via `php artisan storage:link`

### Real-time Features
While currently using log driver for broadcasting, the structure supports:
- Pusher or Redis for real-time updates
- Event broadcasting for notifications and live features
- Private channels for user-specific communications
- HOA expenses updates broadcast on `hoa-expenses` channel with event name `hoa-expenses-updated`

### Database Relationships
Key relationships to understand:
- Resident belongsTo Household
- Household hasMany Resident
- Household belongsTo Family
- FinancialRecord belongsTo Resident
- IssueReport belongsTo Resident
- Conversation hasMany Message through ConvParticipant
- HoaExpense: Standalone model for tracking HOA expenditures
- ReportTemplate belongsTo many Reports through resident
- Report belongsTo Resident and ReportTemplate

### Working with Reports and Templates
- Report templates are managed via `/api/report-templates` (admin/officer only)
- Residents can generate reports from templates via `/api/reports/generate/{templateId}`
- Templates use `{{{variable_name}}}` syntax for variable substitution
- Generated reports are stored and accessible via `/api/reports/my` (resident only)

### HOA Expenses Transparency
- New feature allowing residents to view how HOA fees are utilized
- Accessible via `/api/hoa-expenses` (resident only)
- Expenses are broadcast in real-time when created/updated/deleted
- Sample data includes landscaping, pool maintenance, security, insurance, and utilities