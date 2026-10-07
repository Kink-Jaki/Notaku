# POS Order App

Aplikasi Point of Sale (POS) & Marketplace untuk manajemen produk, pesanan, pembayaran Xendit, dan laporan.

## Tech Stack
- Laravel 13 (PHP 8.5)
- PostgreSQL
- Vite + Bootstrap 5
- Xendit Payment Gateway

## Local Development
```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
npm run dev
```

## Testing
```bash
php artisan test
vendor/bin/pint --test
```

## Deployment
- [Railway Guide](DEPLOY_RAILWAY.md)
- [Render Guide](DEPLOY_RENDER.md)

Health Check: `/health`

## Production Checklist
- [ ] Set `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Generate APP_KEY
- [ ] Configure PostgreSQL DB
- [ ] Set proper `APP_URL` (HTTPS)
- [ ] Configure SMTP & mail
- [ ] Configure Xendit credentials & callback URL
- [ ] Run `php artisan migrate --force`
- [ ] Build assets (`npm run build`)
- [ ] Cache config/routes/views: `php artisan optimize`
- [ ] Enable HTTPS & secure cookies
