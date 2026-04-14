# CareNest — Vercel + Neon Deployment Guide

## Stack
- **Hosting**: Vercel (free Hobby tier)
- **Database**: Neon PostgreSQL (free tier)
- **File Storage**: Cloudinary (free tier)
- **PHP Runtime**: `vercel-php@0.7.2` (community runtime)

---

## Prerequisites

- [Vercel account](https://vercel.com) (free)
- [Neon account](https://neon.tech) (free)
- [Cloudinary account](https://cloudinary.com) (free)
- Vercel CLI installed: `npm i -g vercel`
- Node.js and Composer available locally

---

## One-Time Code Changes

These changes are already committed to this repo. Documented here for reference.

### 1. Cloudinary package

```bash
composer require cloudinary-labs/cloudinary-laravel
```

Added the `cloudinary` disk to `config/filesystems.php`:

```php
'cloudinary' => [
    'driver'     => 'cloudinary',
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key'    => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),
    'secure'     => true,
],
```

### 2. Writable storage on Vercel (`bootstrap/app.php`)

Vercel's filesystem is read-only at runtime. Laravel needs writable paths for
compiled views, logs, and framework cache. The fix redirects them to `/tmp`:

```php
$app = Application::configure(...)->create();

if (isset($_ENV['VERCEL'])) {
    $storagePath   = '/tmp/storage';
    $bootstrapPath = '/tmp/bootstrap';

    foreach ([
        $storagePath.'/app/public',
        $storagePath.'/framework/cache/data',
        $storagePath.'/framework/sessions',
        $storagePath.'/framework/views',
        $storagePath.'/logs',
        $bootstrapPath.'/cache',
    ] as $dir) {
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    $app->useStoragePath($storagePath);
    $app->useBootstrapPath($bootstrapPath);
}

return $app;
```

### 3. `vercel.json`

```json
{
  "version": 2,
  "framework": null,
  "buildCommand": "npm run build",
  "outputDirectory": "public",
  "functions": {
    "api/index.php": {
      "runtime": "vercel-php@0.7.2"
    }
  },
  "routes": [
    {
      "src": "/build/(.*)",
      "headers": { "cache-control": "public, max-age=31536000, immutable" },
      "dest": "/public/build/$1"
    },
    {
      "src": "/(favicon\\.ico|robots\\.txt|apple-touch-icon.*|logo.*\\.png)",
      "dest": "/public/$1"
    },
    {
      "src": "/(.*\\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|webp|avif|webmanifest|map))",
      "dest": "/public/$1"
    },
    {
      "src": "/(.*)",
      "dest": "/api/index.php"
    }
  ]
}
```

**Key decisions:**
- `"framework": null` — prevents Vercel auto-detecting this as a Next.js/Vite app
- `"outputDirectory": "public"` — Laravel serves from `public/`, not `dist/`
- `"buildCommand": "npm run build"` — Composer is not available in Vercel's build environment; `vendor/` is committed instead (see below)

### 4. `api/index.php` (Vercel entry point)

```php
<?php

require __DIR__.'/../public/index.php';
```

### 5. `vendor/` committed to git

Vercel's build environment does not have PHP or Composer available during the
build phase. The `vercel-php` runtime only provides PHP for request handling,
not for builds.

**Solution:** commit `vendor/` to the repository.

In `.gitignore`, the `/vendor` line was commented out:

```
# /vendor - committed for Vercel deployment (no PHP in build environment)
```

Run locally before committing:

```bash
composer install --no-dev --optimize-autoloader
```

### 6. Removed `nativephp/mobile`

Vercel serverless functions have a **250 MB unzipped size limit**. The
`nativephp/mobile` package (a mobile app framework) was not needed for web
deployment and was the primary cause of exceeding this limit. It was removed
from `composer.json`.

### 7. `.vercelignore`

Excludes dev-only vendor packages and test/doc directories from the deployed
bundle to stay under the 250 MB limit:

```
node_modules
tests
.git
storage/logs
storage/framework/cache
storage/framework/sessions
storage/framework/views
bootstrap/cache
database/database.sqlite
nativephp

vendor/*/tests
vendor/*/Tests
vendor/*/test
vendor/*/Test
vendor/*/docs
vendor/*/doc
vendor/*/documentation
vendor/*/examples
vendor/*/example
vendor/*/benchmarks
vendor/*/demos
vendor/phpunit
vendor/mockery
vendor/fakerphp
vendor/nunomaduro
vendor/laravel/pint
vendor/laravel/sail
vendor/laravel/pail
vendor/sebastian
vendor/theseer
vendor/staabm
```

---

## Database Setup (Neon)

### Create a Neon project

1. Go to [console.neon.tech](https://console.neon.tech)
2. Create a new project (e.g. `carenest`)
3. Note your connection strings — Neon provides two:
   - **Pooled** (via PgBouncer): for the running app
   - **Direct** (unpooled): for running migrations

### Run migrations

> **Important:** Always use the **direct** (non-pooled) connection string when
> running migrations. The pooled connection goes through PgBouncer in
> transaction mode, which aborts DDL transactions mid-flight and causes
> cascading `current transaction is aborted` errors.

Set in local `.env` temporarily:

```env
DB_CONNECTION=pgsql
DB_URL=postgresql://user:password@ep-xxxx.us-east-1.aws.neon.tech/neondb?sslmode=require
```

Then:

```bash
php artisan migrate --force
```

---

## Environment Variables (Vercel)

Set these in the Vercel dashboard under **Project → Settings → Environment Variables**,
or via CLI with `vercel env add <NAME>`.

| Variable | Value |
|---|---|
| `APP_NAME` | CareNest |
| `APP_ENV` | production |
| `APP_KEY` | Run `php artisan key:generate --show` locally |
| `APP_DEBUG` | false |
| `APP_URL` | Your Vercel deployment URL |
| `DB_CONNECTION` | pgsql |
| `DB_URL` | Neon **pooled** connection string |
| `SESSION_DRIVER` | database |
| `CACHE_STORE` | database |
| `QUEUE_CONNECTION` | sync |
| `LOG_CHANNEL` | stderr |
| `FILESYSTEM_DISK` | cloudinary |
| `CLOUDINARY_CLOUD_NAME` | From Cloudinary dashboard |
| `CLOUDINARY_API_KEY` | From Cloudinary dashboard |
| `CLOUDINARY_API_SECRET` | From Cloudinary dashboard |
| `VERCEL` | 1 |

> AI provider keys (`GROQ_API_KEY`, `GEMINI_API_KEY`) and mail settings
> should also be added if those features are needed.

---

## Deploying

### First deploy

```bash
# Install Vercel CLI
npm i -g vercel

# Login
vercel login

# From the project root — follow prompts to create a new project
vercel

# Deploy to production
vercel --prod
```

### Subsequent deploys

```bash
# After committing changes (including any vendor/ updates)
vercel --prod
```

Or connect the GitHub repo to Vercel for automatic deployments on push.

---

## Known Limitations on Vercel

| Feature | Status | Notes |
|---|---|---|
| Web routes, Livewire, Flux UI | Works | |
| Neon PostgreSQL | Works | |
| Cloudinary file uploads | Works | |
| Sessions (database driver) | Works | |
| Cache (database driver) | Works | |
| AI features (Groq / Gemini) | Works | |
| PDF / Word export | Works | Generated in `/tmp`, downloaded immediately |
| Background queue jobs | Partial | `QUEUE_CONNECTION=sync` — jobs run synchronously in the request |
| Scheduled tasks (`schedule:run`) | Not available | Vercel has no persistent process; use an external cron service |
| `php artisan` commands | Not available | Run against Neon directly from local machine |

---

## Updating Vendor After Composer Changes

Whenever you add, remove, or update a package:

```bash
composer install --no-dev --optimize-autoloader
git add vendor/ composer.json composer.lock
git commit -m "Update vendor"
vercel --prod
```
