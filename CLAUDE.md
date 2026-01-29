# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CareNest is a Laravel 12 application using Livewire v4 for reactive components, Tailwind CSS v4 for styling, and Flux UI for the component library. It includes authentication via Laravel Fortify with two-factor authentication support.

## Development Commands

```bash
# Initial setup (install deps, generate key, run migrations, build assets)
composer setup

# Start development servers (PHP server, queue listener, Vite - runs concurrently)
composer dev

# Run full test suite (clears config, lints, runs PHPUnit)
composer test

# Run a single test file
./vendor/bin/phpunit tests/Feature/Auth/AuthenticationTest.php

# Run a single test method
./vendor/bin/phpunit --filter test_users_can_authenticate

# Lint code with Laravel Pint
composer lint

# Check linting without fixing
composer test:lint
```

## Architecture

### Tech Stack
- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Livewire v4, Tailwind CSS v4, Flux UI, Vite 7
- **Auth**: Laravel Fortify with two-factor authentication
- **Roles/Permissions**: Spatie Laravel Permission
- **Database**: SQLite (dev), configurable for production

### Roles System
Four MVP roles with permission-based access:
- `system_admin` - Full system access, user management
- `care_home_manager` - Operational oversight, staff/resident management
- `nurse` - Clinical focus (medications, vitals, care plans)
- `caregiver` - Daily care tasks, resident activities

Users can have multiple roles. Use `@can('permission-name')` in Blade for permission checks.

### Key Directories
- `app/Actions/Fortify/` - Authentication business logic (user creation, password reset)
- `app/Concerns/` - Reusable validation traits (`PasswordValidationRules`, `ProfileValidationRules`)
- `app/Livewire/` - Livewire component classes
- `resources/views/pages/` - Blade templates organized by feature (auth/, settings/, dashboard/)
- `resources/views/components/dashboard/` - Reusable dashboard widgets (stat-card, empty-state, etc.)
- `resources/views/flux/` - Flux UI component overrides
- `database/seeders/` - RolePermissionSeeder, TestUserSeeder

### Patterns
- **Action Classes**: Business logic extracted to action classes in `app/Actions/`
- **Traits for Validation**: Validation rules centralized in `app/Concerns/` traits
- **Livewire for Reactivity**: Settings pages use Livewire components
- **Database Sessions/Queue**: Both sessions and jobs use database driver

### Routes
- Main routes: `routes/web.php`
- Settings routes: `routes/settings.php` (profile, password, appearance, two-factor)
- Auth routes managed by Fortify

### Testing
- PHPUnit with in-memory SQLite
- Feature tests in `tests/Feature/` (auth flows covered)
- Unit tests in `tests/Unit/`

### Test Users (password: `password`)
- `admin@carenest.test` - System Administrator
- `manager@carenest.test` - Care Home Manager
- `nurse@carenest.test` - Nurse
- `caregiver@carenest.test` - Caregiver
- `supervisor@carenest.test` - Multi-role (Nurse + Manager)
- `newuser@carenest.test` - No role assigned
