# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CareNest is an AI-enhanced care home management system built with Laravel 12, Livewire v4, Tailwind CSS v4, and Flux UI. It provides role-based dashboards, user/role management, system settings, resident management, care plans, clinical workflows (medications, vitals, incidents), therapy management with session documentation, and AI integration (Groq + Google Gemini) for report generation, document analysis, care assistance, incident summarization, and therapy session reporting.

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

# Create super admin via CLI
php artisan carenest:create-super-admin

# Clear seeded test users
php artisan carenest:clear-test-users

# Build assets for production / ngrok usage
npm run build
```

## Architecture

### Tech Stack
- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Livewire v4, Tailwind CSS v4, Flux UI, Vite 7
- **Auth**: Laravel Fortify with two-factor authentication
- **Roles/Permissions**: Spatie Laravel Permission
- **AI**: Groq (Llama 3.3, Qwen 3) + Google Gemini (2.0/2.5) via HTTP facade
- **Database**: SQLite (dev), configurable for production

### Roles & Permissions
Five roles with 19 permissions across 7 groups:
- `system_admin` - Full system access (all 19 permissions)
- `care_home_manager` - Operational oversight (15 permissions)
- `nurse` - Clinical focus (10 permissions)
- `caregiver` - Daily care tasks (5 permissions)
- `therapist` - Therapy focus (5 permissions: view-residents, view-care-plans, view-therapy, conduct-therapy, view-reports)

Permission groups: User & System, Residents, Staff, Clinical, Therapy, Activities & Incidents, Reports.

Users can have multiple roles and direct permission overrides. Use `@can('permission-name')` in Blade.

### Key Directories
- `app/Actions/Fortify/` - Authentication business logic (user creation, password reset)
- `app/Concerns/` - Validation traits (`PasswordValidationRules`, `UserValidationRules`, `GeneralSettingsValidationRules`, `AiSettingsValidationRules`, `ResidentValidationRules`, `CarePlanValidationRules`, `MedicationValidationRules`, `VitalValidationRules`, `IncidentValidationRules`)
- `app/Contracts/` - Interfaces (`AiProvider`)
- `app/DataObjects/` - Value objects (`AiResponse`)
- `app/Models/` - Eloquent models (`User`, `SystemSetting`, `Resident`, `CarePlan`, `Medication`, `MedicationLog`, `Vital`, `Incident`)
- `app/Services/` - Business logic services (`SettingsService`, `AI/GroqProvider`, `AI/GeminiProvider`, `AI/AiManager`)
- `app/Console/Commands/` - Artisan commands (`CreateSuperAdminCommand`, `ClearTestUsersCommand`)
- `resources/views/pages/` - Inline Livewire page components organized by feature
- `resources/views/components/` - Reusable Blade components (dashboard widgets, admin layouts)
- `database/seeders/` - `RolePermissionSeeder`, `TestUserSeeder`, `SystemSettingsSeeder`, `ResidentSeeder`, `CarePlanSeeder`, `MedicationSeeder`, `MedicationLogSeeder`, `VitalSeeder`, `IncidentSeeder`

### Patterns
- **Inline Livewire Components**: Pages use `⚡` prefix files with PHP + Blade in one file, using `#[Layout]`, `#[Title]`, `#[Computed]`, `#[Locked]`, `#[Url]` attributes
- **Traits for Validation**: Validation rules centralized in `app/Concerns/` traits
- **Service Layer**: `SettingsService` for cached key-value settings, `AiManager` as factory/orchestrator for AI providers
- **Global Helper**: `system_setting('key', 'default')` function available everywhere
- **Action Classes**: Business logic in `app/Actions/`
- **Database Sessions/Queue**: Both sessions and jobs use database driver
- **SoftDeletes**: Used on resident-facing models (`Resident`, `CarePlan`, `Medication`, `Incident`) for data safety
- **Audit Fields**: `created_by`, `updated_by`, `reviewed_by` FK fields on models for traceability

### Routes
- Main routes: `routes/web.php`
- Admin routes: `routes/admin.php` (user management, role management, system settings)
- Resident routes: `routes/residents.php` (residents CRUD, care plans CRUD)
- Clinical routes: `routes/clinical.php` (medications, vitals, incidents)
- Settings routes: `routes/settings.php` (profile, password, appearance, two-factor)
- Auth routes managed by Fortify

### Admin Module (`routes/admin.php`)
- `/admin/users` - User list with search, filter, pagination, delete
- `/admin/users/create` - Create user with role assignment
- `/admin/users/{user}/edit` - Edit user profile, password, roles, permission overrides
- `/admin/roles` - Role list with user/permission counts
- `/admin/roles/{role}/edit` - Edit role permissions with select all/deselect all
- `/admin/settings/general` - System name, branding, contact info, social links
- `/admin/settings/ai` - AI provider config (Groq/Gemini), use case settings
- `/admin/settings/chatbot` - Live AI test chat interface

### Residents Module (`routes/residents.php`)
- `/residents` - Resident list with search, status filter, pagination
- `/residents/create` - Create resident with personal, admission, medical, emergency contact, next of kin info
- `/residents/{resident}` - Resident detail with care plans, active medications, recent vitals, incidents
- `/residents/{resident}/edit` - Edit resident
- `/care-plans` - Care plan list with search, type/status filters
- `/care-plans/{carePlan}` - Care plan detail
- `/care-plans/{carePlan}/edit` - Edit care plan
- `/residents/{resident}/care-plans/create` - Create care plan for a specific resident

### Clinical Module (`routes/clinical.php`)
- `/medications` - Medication list with search, status/route filters (permission: `manage-medications`)
- `/medications/create` - Create medication prescription
- `/medications/{medication}` - Medication detail with administration history
- `/medications/{medication}/edit` - Edit medication
- `/medications/{medication}/administer` - Log medication administration (permission: `administer-medications`)
- `/vitals` - Vitals list with resident search, date range filters (permission: `manage-medications`)
- `/vitals/record` - Record vital signs (BP, HR, temp, RR, SpO2, blood sugar, weight, pain, AVPU)
- `/vitals/{vital}` - Vital signs detail with abnormal value highlighting
- `/incidents` - Incident list with search, type/severity/status filters (permission: `manage-incidents`)
- `/incidents/create` - Report incident (permission: `report-incidents`)
- `/incidents/{incident}` - Incident detail with severity/status badges
- `/incidents/{incident}/edit` - Edit incident with auto-set reviewer on status change

### System Settings
Key-value storage in `system_settings` table with groups: `general`, `branding`, `contact`, `social`, `ai`. Settings are cached via `SettingsService` and shared globally through `AppServiceProvider`. API keys are encrypted with Laravel's `Crypt` facade.

### AI Integration
- **Providers**: Groq (text, fastest) and Google Gemini (multimodal, document analysis)
- **Use Cases**: Report Generation, Document Analysis, Care Assistant, Incident Summarization
- **Architecture**: `AiProvider` interface -> `GroqProvider`/`GeminiProvider` implementations -> `AiManager` factory
- **No external packages** - uses Laravel `Http` facade for API calls
- **Groq Models**: `llama-3.3-70b-versatile`, `llama-3.1-8b-instant`, `meta-llama/llama-4-scout-17b-16e-instruct`, `qwen/qwen3-32b`
- **Gemini Models**: `gemini-2.0-flash`, `gemini-2.5-flash`, `gemini-2.5-pro`

### Proxy / Ngrok
Trusted proxies are configured in `bootstrap/app.php` with `$middleware->trustProxies(at: '*')`. To expose the app via ngrok: run `npm run build` first (Vite dev server is not reachable through the tunnel), then `php artisan serve` and `ngrok http 8000`.

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
