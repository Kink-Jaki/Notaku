# Deployment Guide - Render

## Pre-requisites
- Render account (https://render.com/)
- PostgreSQL instance on Render

## Step 1: Create Web Service
1. New → Web Service
2. Connect GitHub repo
3. Build & deploy from branch `main`

## Step 2: Configure
Environment: PHP
Build Command:
```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Start Command:
```bash
php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
```

## Step 3: Environment Variables
Same as Railway, use Render's internal DB vars or manual Postgres connection.

Add `healthcheckPath: /health` in advanced settings if available.

## Health Check
`GET https://your-app.onrender.com/health`
