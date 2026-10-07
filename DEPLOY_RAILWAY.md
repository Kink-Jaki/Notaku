# Deployment Guide - Railway

## Pre-requisites
- Railway account (https://railway.app/)
- PostgreSQL database on Railway
- GitHub repo connected

## Step 1: Create Project on Railway
1. Login ke Railway, buat project baru
2. Connect GitHub repo `pos-order-app`
3. Add service "Database" → PostgreSQL

## Step 2: Set Environment Variables
Di Railway Dashboard → Service (app) → Variables, tambahkan:

```env
APP_NAME=POS Order App
APP_ENV=production
APP_KEY= # generate via php artisan key:generate --show (run locally)
APP_DEBUG=false
APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_HOST=${{PGHOST}}
DB_PORT=${{PGPORT}}
DB_DATABASE=${{PGDATABASE}}
DB_USERNAME=${{PGUSER}}
DB_PASSWORD=${{PGPASSWORD}}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="POS Order App"

XENDIT_SECRET_KEY=your-xendit-secret-key
XENDIT_CALLBACK_TOKEN=your-xendit-callback-token
XENDIT_CALLBACK_URL=${{RAILWAY_PUBLIC_DOMAIN}}/webhook/xendit
XENDIT_INVOICE_DURATION=3600
```

## Step 3: Build & Deployment
Railway akan otomatis build dengan Nixpacks. Pastikan `composer.json` & `package.json` ada.

Tambahkan `railway.json` (optional) atau gunakan default:

```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "NIXPACKS"
  },
  "deploy": {
    "startCommand": "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT",
    "healthcheckPath": "/health",
    "healthcheckTimeout": 300,
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  }
}
```

## Step 4: Post-Deploy
- Run migrations: `railway run php artisan migrate --force`
- Generate key: `railway run php artisan key:generate --show` (copy ke APP_KEY)
- Link storage: `railway run php artisan storage:link` (jika perlu)
- Check health: `https://your-app.up.railway.app/health`

## Notes
- Health endpoint: `GET /health` returns JSON status ok
- APP_DEBUG harus false di production
- SESSION_SECURE_COOKIE true (butuh HTTPS)
- Xendit callback URL wajib menggunakan HTTPS
