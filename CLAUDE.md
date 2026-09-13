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
```

### Development Server
```bash
# Start development server with concurrent processes
npm run dev
# Or use the configured script:
composer run dev
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
```

### Code Quality
```bash
# Run Laravel Pint (code formatter)
composer run format

# Run static analysis
composer run analyse
```

### Database
```bash
# Reset and re-seed database
php artisan migrate:fresh --seed

# Create migration
php artisan make:migration create_table_name

# Create model
php artisan make:model ModelName
```

## Code Architecture

### Laravel Structure
This is a Laravel 13 application following standard MVC conventions:

- **App/Http/Controllers**: Contains all HTTP controllers handling requests
- **App/Models**: Eloquent models representing database tables
- **Routes/web.php**: Defines web routes mapping URLs to controller methods
- **Resources/Views**: Blade templates for rendering HTML
- **Database/Migrations**: Database schema version control
- **Database/Seeders**: Sample data for development/testing
- **App/Http/Middleware**: Request filtering (authentication, etc.)

### Key Features Implemented
Based on the codebase examination:

1. **Authentication System**: Custom auth controllers (`AuthController.php`) with separate guards for admins, officers, and residents
2. **Announcements**: CRUD functionality for community announcements with status tracking
3. **Financial Management**: Tracking of financial records and reports
4. **Issue Tracking**: System for reporting and managing community issues
5. **Messaging**: Communication system between users
6. **File Management**: Document storage for officers and residents
7. **Dashboard**: Administrative and user-facing dashboards
8. **Mapping**: Geographic visualization of community data

### Common Patterns
- **Authentication**: Uses custom middleware (`AdminAuth.php`, `OfficerAuth.php`) for role-based access control
- **Validation**: Form requests likely used for input validation (check controllers for validation logic)
- **Eloquent Relationships**: Models define relationships (check model files for `hasMany`, `belongsTo`, etc.)
- **Resource Controllers**: Standard RESTful controllers for CRUD operations
- **Blade Templating**: Layout-based views with shared layouts in `resources/views/layouts/`

### Environment
- Uses SQLite for development (`database/database.sqlite`)
- Environment variables managed through `.env` file
- Queue system configured for database jobs
- Broadcasting set up for real-time features (currently using log driver)

### API Design
The application follows a RESTful API design pattern:
- API routes are prefixed with `/api`
- Resource controllers handle standard CRUD operations
- Custom endpoints are added for specific functionality (e.g., `/api/dashboard-data` for combined data)
- Response format is JSON with consistent structure

### Performance Considerations
- The dashboard endpoint (`/api/dashboard-data`) combines multiple queries into a single endpoint to reduce HTTP overhead
- Eloquent eager loading is used to prevent N+1 query problems
- Selective column selection minimizes data transfer

## Laravel Boost
This repository has Laravel Boost installed for AI-assisted development:
- Provides 15+ tools and skills for agents like Claude Code
- Installed via `composer require laravel/boost --dev`
- Configured with `php artisan boost:install`

## Testing Conventions
- Feature tests in `tests/Feature/` directory
- Unit tests in `tests/Unit/` directory
- Uses Laravel's built-in testing utilities with PHPUnit
- Tests database transactions with `RefreshDatabase` trait where needed

## Important Files to Review
- `routes/web.php` - Understand application routes and controller mappings
- `app/Http/Controllers/` - Review controller logic for business rules
- `app/Models/` - Understand data structure and relationships
- `database/migrations/` - Review database schema evolution