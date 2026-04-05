# CareNest — Vercel Deployment Guide

> **Important note before you start:** Vercel is a JavaScript/Node.js-first platform and does **not** natively support PHP or Laravel. CareNest is a full-stack Laravel + Livewire app that requires a persistent PHP runtime, database sessions, a queue worker, and writable storage — none of which fit Vercel's serverless model.
>
> **Recommended architecture:**
> - **App server (Laravel)** → Railway (you already have `railway.toml` + `Dockerfile`)
> - **Frontend asset CDN** → Vercel (optional, serves the Vite-built CSS/JS faster globally)
>
> This guide covers both: getting the app live on Railway first, then optionally wiring Vercel as a CDN for static assets.

---

## Prerequisites

- Git repository pushed to GitHub
- [Railway account](https://railway.app) (free tier available)
- [Vercel account](https://vercel.com) (for CDN, optional)
- A PostgreSQL database (Railway provides one, or use [Neon](https://neon.tech) free tier)
- A file storage bucket (Cloudflare R2 free tier or AWS S3)

---

## Part 1 — Deploy the Laravel App on Railway

Railway runs your existing `Dockerfile` with zero extra config. You already have `railway.toml` set up.

### 1.1 Create a New Railway Project

1. Go to [railway.app](https://railway.app) → **New Project**
2. Choose **Deploy from GitHub repo**
3. Select your CareNest repository
4. Railway auto-detects the `Dockerfile` and `railway.toml`

### 1.2 Add a PostgreSQL Database

In the Railway project dashboard:

1. Click **+ Add Service** → **Database** → **PostgreSQL**
2. Railway injects `DATABASE_URL` automatically into your environment

### 1.3 Set Environment Variables

In the Railway service → **Variables** tab, add:

```env
APP_NAME=CareNest
APP_ENV=production
APP_DEBUG=false
APP_KEY=                        # generate below
APP_URL=https://your-domain.up.railway.app

DB_CONNECTION=pgsql
DATABASE_URL=${DATABASE_URL}    # auto-injected by Railway PostgreSQL

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=s3

# Storage (Cloudflare R2 or AWS S3)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=auto
AWS_BUCKET=carenest-uploads
AWS_ENDPOINT=                   # R2 endpoint if using Cloudflare

# AI providers
GROQ_API_KEY=
GEMINI_API_KEY=

# Mail (Brevo / Mailgun / SES)
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=CareNest
```

**Generate APP_KEY locally and paste it in:**

```bash
php artisan key:generate --show
```

### 1.4 Switch from SQLite to PostgreSQL

The `Dockerfile` `CMD` already runs `php artisan migrate --force`. For PostgreSQL you need to:

1. Confirm the `pgsql` extension is installed — it already is (`pdo_pgsql` is in the Dockerfile)
2. The `DATABASE_URL` variable is automatically parsed by Laravel when `DB_CONNECTION=pgsql`

> If you want to keep SQLite on Railway, mount a Railway Volume at `/app/database` and set `DB_DATABASE=/app/database/database.sqlite`. This works but is not recommended for production.

### 1.5 Set Up File Storage (Required — Railway has no persistent disk)

Railway containers are ephemeral. All uploaded files (resident photos, PDF exports, CMS images) **must** go to object storage.

**Recommended: Cloudflare R2 (free 10 GB/month)**

1. Create a Cloudflare account → R2 → Create bucket `carenest-uploads`
2. Create an API token with R2 read/write permissions
3. Set these env vars in Railway:
   ```env
   AWS_ACCESS_KEY_ID=<r2-access-key>
   AWS_SECRET_ACCESS_KEY=<r2-secret-key>
   AWS_BUCKET=carenest-uploads
   AWS_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
   AWS_DEFAULT_REGION=auto
   FILESYSTEM_DISK=s3
   ```

### 1.6 Add a Queue Worker (for AI jobs and email)

CareNest uses `QUEUE_CONNECTION=database`. Railway runs a single process per service. To run the queue worker alongside the web server, add to your `Dockerfile` CMD — or create a second Railway service:

**Option A — Second Railway service (recommended)**

1. In the same Railway project, click **+ Add Service** → **GitHub Repo** (same repo)
2. Override the start command:
   ```
   php artisan queue:work --sleep=3 --tries=3 --max-time=3600
   ```
3. Set the same environment variables as the web service

**Option B — Single-process workaround (quick/dirty)**

Not recommended for production, but works for demos:
```dockerfile
CMD ["sh", "-c", "php artisan config:cache && php artisan migrate --force && php artisan queue:work &  php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
```

### 1.7 Custom Domain (optional)

1. Railway service → **Settings** → **Domains** → **Add Custom Domain**
2. Add the CNAME record your DNS provider
3. Railway provisions a Let's Encrypt SSL certificate automatically
4. Update `APP_URL` in environment variables to match

### 1.8 Verify the Deployment

```
https://your-app.up.railway.app
```

Checklist:
- [ ] Home page loads
- [ ] Login works
- [ ] Create a resident → photo upload saves to R2
- [ ] Run a therapy AI report → queue processes it

---

## Part 2 — Vercel as a Static Asset CDN (Optional)

If you want faster global delivery of the compiled CSS/JS, you can push the `public/build` output to Vercel and update Laravel to load assets from the Vercel CDN URL.

### 2.1 Create a Vercel Project for Assets

1. In Vercel, create a new project → **Import Git Repository** → select CareNest
2. Set the **Framework Preset** to `Other`
3. Set **Build Command**:
   ```bash
   npm ci && npm run build
   ```
4. Set **Output Directory**: `public/build`
5. Set **Install Command**: `npm ci`

Vercel will build and deploy the `public/build` directory on every push.

### 2.2 Set the Asset URL in Laravel

In Railway environment variables, add:

```env
ASSET_URL=https://your-vercel-project.vercel.app
```

Laravel's `asset()` and Vite helper will now load CSS/JS from the Vercel CDN instead of your Railway server.

### 2.3 Update Vite Config for the CDN Base URL

In `vite.config.js`, the `laravel-vite-plugin` uses the manifest to resolve asset URLs automatically — no changes needed if `ASSET_URL` is set correctly.

### 2.4 Custom Domain on Vercel (optional)

In Vercel → Project → **Settings** → **Domains**, add `assets.yourdomain.com` and update `ASSET_URL` accordingly.

---

## Part 3 — Environment Variable Reference

| Variable | Required | Description |
|---|---|---|
| `APP_KEY` | ✅ | 32-char Laravel encryption key |
| `APP_URL` | ✅ | Full URL of the Railway app |
| `DB_CONNECTION` | ✅ | `pgsql` (Railway) or `sqlite` |
| `DATABASE_URL` | ✅ | Auto-set by Railway PostgreSQL |
| `FILESYSTEM_DISK` | ✅ | `s3` for R2/S3 |
| `AWS_*` | ✅ | R2 or S3 credentials |
| `GROQ_API_KEY` | ✅ | For AI report generation |
| `GEMINI_API_KEY` | ✅ | For document analysis |
| `MAIL_*` | ✅ | Brevo/SES/Mailgun credentials |
| `ASSET_URL` | optional | Vercel CDN URL for static assets |
| `SESSION_DRIVER` | ✅ | `database` |
| `QUEUE_CONNECTION` | ✅ | `database` |

---

## Part 4 — Seeding Initial Data (First Deploy)

After the first deploy, run the super admin command via Railway's shell:

1. Railway dashboard → service → **Shell** tab
2. Run:
   ```bash
   php artisan carenest:create-super-admin
   ```
3. Follow the prompts to create the first admin account

To seed demo data:
```bash
php artisan db:seed
```

---

## Part 5 — Ongoing Deploys

Every `git push` to your main branch triggers:
1. Railway rebuilds the Docker image
2. Runs `php artisan migrate --force` on container start
3. Zero-downtime rollover (Railway swaps containers)

If using Vercel CDN:
1. Vercel rebuilds `npm run build` in parallel
2. New asset hashes are written to `public/build/manifest.json`
3. Laravel reads the new manifest on next request

---

## Architecture Summary

```
Browser
  │
  ├── Static assets (CSS/JS) ──► Vercel CDN (optional)
  │
  └── All requests ─────────────► Railway
                                    ├── Laravel app (web)
                                    ├── Queue worker (separate service)
                                    ├── PostgreSQL (Railway addon)
                                    └── File uploads ──► Cloudflare R2
```

---

## Cost Estimate

| Service | Tier | Monthly |
|---|---|---|
| Railway (web + worker) | Hobby $5 credit | ~$5–15 |
| Railway PostgreSQL | Included in Hobby | ~$0–5 |
| Cloudflare R2 | Free 10 GB | $0 |
| Vercel (CDN) | Free | $0 |
| **Total** | | **~$5–20/month** |
