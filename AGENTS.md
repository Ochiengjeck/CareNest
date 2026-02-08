# Repository Guidelines

## Project Structure & Module Organization
This is a Laravel 12 + Livewire application. Key directories:
`app/` (application code, helpers, jobs, policies), `routes/` (web/api routes),
`resources/` (Blade views, Livewire components, frontend assets),
`public/` (public web root), `database/` (migrations, seeders, sqlite file),
`tests/` (Feature/Unit), and `config/` (configuration).
Static assets and uploads live in `public/` and `storage/` respectively.

## Build, Test, and Development Commands
- `composer run setup` initializes local dev (installs deps, copies `.env`, generates key, migrates, builds assets).
- `composer run dev` runs Laravel server, queue listener, and Vite dev server concurrently.
- `npm run dev` runs the Vite dev server only.
- `npm run build` builds production assets.
- `composer run lint` runs PHP formatting via Pint.
- `composer run test` clears config, runs Pint in test mode, then runs the test suite.

## Coding Style & Naming Conventions
- Indentation: 4 spaces (per `.editorconfig`). YAML uses 2 spaces.
- PHP formatting: Laravel Pint (`pint.json` uses the `laravel` preset).
- Test names follow `*Test.php` (see `tests/Feature/Auth/AuthenticationTest.php`).
- Use Laravel conventions for class/file names (StudlyCase classes, matching filenames).

## Testing Guidelines
- Framework: PHPUnit (via `php artisan test`).
- Tests live in `tests/Feature` and `tests/Unit`.
- Use the existing naming pattern `SomethingTest.php`.
- Recommended: keep tests isolated and rely on SQLite in-memory config from `phpunit.xml`.

## Commit & Pull Request Guidelines
- Commit messages are short, verb-led, and sentence-style (e.g., “fixed …”, “implemented …”, “Add …”).
- PRs should include: a brief summary, linked issue/ticket when applicable, and screenshots for UI changes.
- Highlight any config changes (`.env`, `config/`, migrations) and note required follow-up steps.

## Security & Configuration Tips
- Copy `.env.example` to `.env` and keep secrets out of git.
- Database setup is in `database/`; local development uses SQLite by default during tests.
