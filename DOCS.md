# POS & Order — Dokumentasi Project

> Sistem kasir & pemesanan berbasis Laravel 13 + Supabase (PostgreSQL). Single outlet F&B/toko sembako.

---

## Daftar Isi

1. [Ikhtisar Project](#1-ikhtisar-project)
2. [Struktur Directory](#2-struktur-directory)
3. [Instalasi & Setup](#3-instalasi--setup)
4. [Database & Migrations](#4-database--migrations)
5. [Models & Eloquent Relationships](#5-models--eloquent-relationships)
6. [Routes & Authorization](#6-routes--authorization)
7. [Middleware Role](#7-middleware-role)
8. [Layout & Blade Templates](#8-layout--blade-templates)
9. [Blade Components](#9-blade-components)
10. [Design System & CSS](#10-design-system--css)
11. [JavaScript & SweetAlert2](#11-javascript--sweetalert2)
12. [Controllers Overview](#12-controllers-overview)
13. [Vite & Frontend Bundling](#13-vite--frontend-bundling)
14. [Configuration Files](#14-configuration-files)
15. [Deployment](#15-deployment)

---

## 1. Ikhtisar Project

| Aspek | Detail |
|---|---|
| **Nama** | POS & Order |
| **Framework** | Laravel 13.17 |
| **PHP** | ^8.3 |
| **Database** | Supabase PostgreSQL (via `pgsql` driver) |
| **Frontend CSS** | Bootstrap 5.3 + Bootstrap Icons + Tailwind |
| **Frontend JS** | Vite + Bootstrap JS + SweetAlert2 |
| **Font** | Inter (Google Fonts) |
| **Role Sistem** | Pelanggan, Kasir, Admin |
| **Alur Pesanan** | Pelanggan checkout → status `pending` → Kasir approve/reject |
| **Pembayaran** | Klik "Order" -> data keranjang (frontend) baru dikirim ke database |

### Tiga Role

- **Pelanggan** — Marketplace: bisa lihat katalog, pesan, checkout, lihat riwayat pesanan
- **Kasir** — Proses transaksi: lihat antrian, proses POS, lihat riwayat, cetak struk, laporan
- **Admin** — Kelola user, promo, produk, dan laporan

---

## 2. Struktur Directory

```
pos-order-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php                    # Abstract base controller
│   │   │   ├── CartController.php                # Legacy cart (deprecated)
│   │   │   ├── CatalogController.php             # Legacy catalog (deprecated)
│   │   │   ├── OrderController.php               # Legacy order (deprecated)
│   │   │   ├── ProfileController.php             # User profile
│   │   │   ├── Admin/
│   │   │   │   ├── AdminDashboardController.php
│   │   │   │   ├── AdminUserController.php
│   │   │   │   ├── AdminProdukController.php
│   │   │   │   └── AdminPromoController.php
│   │   │   ├── Kasir/
│   │   │   │   ├── KasirDashboardController.php
│   │   │   │   ├── KasirPosController.php
│   │   │   │   ├── KasirAntrianController.php
│   │   │   │   ├── KasirRiwayatController.php
│   │   │   │   ├── KasirStrukController.php
│   │   │   │   ├── KasirLaporanHarianController.php
│   │   │   │   └── KasirLaporanBulananController.php
│   │   │   ├── Pelanggan/
│   │   │   │   ├── PelangganKatalogController.php
│   │   │   │   ├── PelangganCartController.php
│   │   │   │   ├── PelangganCheckoutController.php
│   │   │   │   └── PelangganRiwayatController.php
│   │   │   └── Auth/                             # Laravel Breeze auth controllers
│   │   ├── Middleware/
│   │   │   ├── EnsureRole.php                    # NEW — role-based middleware
│   │   │   └── EnsureUserHasRole.php             # LEGACY — replaced by EnsureRole
│   │   └── Resources/
│   ├── Models/
│   │   ├── User.php                              # Role: pelanggan/kasir/admin
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── Order.php                             # Status: pending/diproses/selesai/ditolak
│   │   ├── OrderItem.php
│   │   ├── PromoCode.php
│   │   ├── Transaction.php                       # Manual kasir transaction
│   │   ├── TransactionItem.php
│   │   └── AuditLog.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   ├── View/
│   │   └── Components/
│   │       ├── AppLayout.php                     # Class-based → layouts.app
│   │       └── GuestLayout.php                   # Class-based → layouts.guest
│   └── Support/
│       └── NumberGenerator.php                   # ORD-YYYYMMDD-NNN / TRX-YYYYMMDD-NNN
├── bootstrap/
│   ├── app.php                                   # Middleware alias registration
│   └── providers.php
├── config/
│   ├── app.php
│   └── database.php
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_09_24_025640_create_categories_table.php
│   │   ├── 2026_09_24_025641_create_products_table.php
│   │   ├── 2026_09_24_025642_create_orders_table.php
│   │   ├── 2026_09_24_025643_create_order_items_table.php
│   │   ├── 2026_09_24_025644_create_transactions_table.php
│   │   ├── 2026_09_24_025645_create_transaction_items_table.php
│   │   ├── 2026_09_24_025646_create_promo_codes_table.php
│   │   ├── 2026_09_24_025647_create_audit_logs_table.php
│   │   └── 2026_09_24_093953_add_description_to_promo_codes_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── public/
│   └── build/                                    # Vite build output
├── resources/
│   ├── css/
│   │   ├── bootstrap.css                         # @import bootstrap + bootstrap-icons
│   │   ├── variables.css                         # CSS custom properties (--color-*)
│   │   ├── layout.css                            # Shell layout + responsive design
│   │   ├── components.css                        # Reusable component styles
│   │   └── app.css                               # Import order: bootstrap → variables → layout → components
│   ├── js/
│   │   ├── app.js                                # Bootstrap JS + SweetAlert2 import
│   │   └── sweetalert-config.js                  # Swal.mixin global config
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php                     # BASE LAYOUT — navbar + sidebar + footer
│   │   │   ├── admin.blade.php                   # Extends layouts.app (role: admin)
│   │   │   ├── kasir.blade.php                   # Extends layouts.app (role: kasir)
│   │   │   ├── pelanggan.blade.php               # Extends layouts.app (role: pelanggan)
│   │   │   └── auth.blade.php                    # Extends layouts.app (no sidebar)
│   │   ├── components/                           # Anonymous Blade components
│   │   │   ├── button.blade.php
│   │   │   ├── card.blade.php
│   │   │   ├── modal.blade.php
│   │   │   ├── badge.blade.php
│   │   │   └── table.blade.php
│   │   ├── admin/                                # Admin views
│   │   ├── kasir/                                # Kasir views
│   │   ├── pelanggan/                            # Pelanggan views
│   │   ├── auth/                                 # Laravel Breeze auth views
│   │   ├── design-system.blade.php               # Visual reference page
│   │   └── welcome.blade.php                     # Landing page
├── routes/
│   ├── web.php                                   # All application routes
│   └── auth.php                                  # Laravel Breeze auth routes
├── .env                                          # DB_CONNECTION=pgsql, Supabase config
├── .env.example
├── composer.json
├── package.json
├── vite.config.js
├── phpunit.xml
└── README.md
```

---

## 3. Instalasi & Setup

### Prasyarat

- PHP 8.3+
- Node.js 18+
- PostgreSQL (Supabase)
- Composer

### Instalasi Cepat

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm install --ignore-scripts
npm run build
```

### Development Mode

```bash
php artisan serve
npm run dev
```

### Konfigurasi Environment (.env)

```env
APP_NAME=POS & Order
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-northeast-2.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.periymgknpwbagcfpmwt
DB_PASSWORD=your_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

---

## 4. Database & Migrations

### Koneksi Database

Menggunakan **Supabase PostgreSQL** via `pgsql` driver. Terhubung melalui connection pooler.

### Daftar Migration

| File | Tabel | Keterangan |
|---|---|---|
| `0001_01_01_000000_create_users_table` | `users` | Termasuk kolom `role` (enum: `pelanggan`, `kasir`, `admin`) |
| `0001_01_01_000001_create_cache_table` | `cache` | Laravel cache |
| `0001_01_01_000002_create_jobs_table` | `jobs` | Laravel queue jobs |
| `2026_09_24_025640_create_categories_table` | `categories` | Kategori produk (name, slug) |
| `2026_09_24_025641_create_products_table` | `products` | Produk (name, price, stock, category_id, image, is_active) |
| `2026_09_24_025642_create_orders_table` | `orders` | Pesanan pelanggan (status, subtotal, total, promo_code_id, approved_by) |
| `2026_09_24_025643_create_order_items` | `order_items` | Item pesanan (order_id, product_id, qty, price) |
| `2026_09_24_025644_create_transactions` | `transactions` | Transaksi kasir manual (user_id, order_id, total, payment_method, status) |
| `2026_09_24_025645_create_transaction_items` | `transaction_items` | Item transaksi kasir |
| `2026_09_24_025646_create_promo_codes` | `promo_codes` | Kode promo (code, type: percent/fixed, value, usage_limit) |
| `2026_09_24_025647_create_audit_logs` | `audit_logs` | Audit trail activity |
| `2026_09_24_093953_add_description_to_promo_codes` | `promo_codes` | Kolom tambahan description |

### Seeders

- `DatabaseSeeder.php` — Seeders utama

---

## 5. Models & Eloquent Relationships

### User (`app/Models/User.php`)

```php
// Role constants
public const ROLE_ADMIN = 'admin';
public const ROLE_KASIR = 'kasir';
public const ROLE_PELANGGAN = 'pelanggan';

// Relationships
public function orders(): HasMany
public function transactions(): HasMany

// Helper methods
public function isAdmin(): bool
public function isKasir(): bool
public function isPelanggan(): bool
```

### Order (`app/Models/Order.php`)

```php
// Status constants
public const STATUS_PENDING = 'pending';
public const STATUS_PROCESSING = 'processing';
public const STATUS_COMPLETED = 'completed';
public const STATUS_REJECTED = 'rejected';

// Relationships
public function user(): BelongsTo
public function items(): HasMany
public function promoCode(): BelongsTo
public function approver(): BelongsTo (users table, approved_by)

// Helper methods
public function isPending(): bool
public function displayLabel(): string    // 'menunggu', 'diproses', 'selesai', 'ditolak'
public function displayBadgeClass(): string // CSS class for status badge
```

### Product (`app/Models/Product.php`)

```php
// Relationships
public function category(): BelongsTo

// Scopes
public function scopeActive($query)
public function scopeFilter($query, array $filters)

// Helper methods
public function isOutOfStock(): bool
public static function findStock(int $productId): ?int
public static function decrementStock(int $productId, int $qty): void
```

### Category (`app/Models/Category.php`)

```php
public function products(): HasMany
```

### PromoCode (`app/Models/PromoCode.php`)

```php
public const TYPE_PERCENT = 'percent';
public const TYPE_FIXED = 'fixed';

// Methods
public function isCurrentlyActive(): bool
public function discountFor(int $subtotal): int
public function getDescriptionAttribute(): string
```

### Transaction (`app/Models/Transaction.php`)

```php
public function user(): BelongsTo
public function order(): BelongsTo
public function items(): HasMany
public function promoCode(): BelongsTo
public function grandTotal(): int
```

### OrderItem / TransactionItem / AuditLog

Standard Eloquent models with `BelongsTo` relationships ke parent models.

---

## 6. Routes & Authorization

### File: `routes/web.php`

Semua route dikelompokkan berdasarkan role dengan middleware `role:pelanggan`, `role:kasir`, `role:admin`.

### Rute Pelanggan (`role:pelanggan`)

| Method | URI | Name | Controller |
|---|---|---|---|
| GET | `/katalog` | `pelanggan.katalog` | `PelangganKatalogController@index` |
| GET | `/produk/{id}` | `pelanggan.produk` | `PelangganKatalogController@show` |
| GET | `/cart` | `pelanggan.cart` | `PelangganCartController@index` |
| GET | `/checkout` | `pelanggan.checkout` | `PelangganCheckoutController@index` |
| GET | `/pesanan-saya` | `pelanggan.pesanan-saya` | `PelangganRiwayatController@index` |

### Rute Kasir (`role:kasir`, prefix: `/kasir`)

| Method | URI | Name | Controller |
|---|---|---|---|
| GET | `/dashboard` | `kasir.dashboard` | `KasirDashboardController@index` |
| GET | `/pos` | `kasir.pos` | `KasirPosController@index` |
| GET | `/antrian` | `kasir.antrian` | `KasirAntrianController@index` |
| GET | `/riwayat` | `kasir.riwayat` | `KasirRiwayatController@index` |
| GET | `/transaksi/{id}` | `kasir.transaksi` | `KasirStrukController@show` |
| GET | `/laporan-harian` | `kasir.laporan-harian` | `KasirLaporanHarianController@index` |
| GET | `/laporan-bulanan` | `kasir.laporan-bulanan` | `KasirLaporanBulananController@index` |
| GET | `/laporan-bulanan/export` | `kasir.laporan-bulanan.export` | `KasirLaporanBulananController@export` |
| POST | `/pos/promo/validate` | `kasir.pos.promoValidate` | `KasirPosController@validatePromo` |
| POST | `/pos/order` | `kasir.pos.order` | `KasirPosController@order` |
| POST | `/antrian/{order}/approve` | `kasir.antrian.approve` | `KasirAntrianController@approve` |
| POST | `/antrian/{order}/reject` | `kasir.antrian.reject` | `KasirAntrianController@reject` |
| GET | `/antrian/check-new` | `kasir.antrian.check-new` | `KasirAntrianController@checkNewOrders` |

### Rute Admin (`role:admin`, prefix: `/admin`)

| Method | URI | Name | Controller |
|---|---|---|---|
| GET | `/dashboard` | `admin.dashboard` | `AdminDashboardController@index` |
| GET/POST | `/users` | `admin.users.*` | `AdminUserController` (resource) |
| GET/POST | `/promo` | `admin.promo.*` | `AdminPromoController` (resource) |
| GET/POST | `/produk` | `admin.produk.*` | `AdminProdukController` (resource) |

### Rute Publik & Auth

| Method | URI | Name | Keterangan |
|---|---|---|---|
| GET | `/` | — | Landing page (welcome.blade.php) |
| GET | `/dashboard` | `dashboard` | Redirect berdasarkan role |
| GET | `/design-system` | `design-system` | Halaman referensi design system |
| Semua | `/login`, `/register`, dll | — | Laravel Breeze auth routes (`routes/auth.php`) |

### Pola Route Naming

Semua route menggunakan prefix naming:
- Pelanggan: `pelanggan.*`
- Kasir: `kasir.*`
- Admin: `admin.*`

---

## 7. Middleware Role

### File: `app/Http/Middleware/EnsureRole.php`

Middleware yang memeriksa role pengguna sebelum mengakses route tertentu.

**Registrasi:** `bootstrap/app.php`
```php
$middleware->alias([
    'role' => EnsureRole::class,
]);
```

**Penggunaan di routes:**
```php
Route::middleware(['auth', 'verified', 'role:pelanggan'])->name('pelanggan.')->group(...);
Route::middleware(['auth', 'verified', 'role:kasir'])->prefix('kasir')->name('kasir.')->group(...);
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(...);
```

**Logika:**
1. Jika user tidak login → `abort(401)`
2. Jika role user tidak cocok → `abort(403)`
3. Jika cocok → lanjutkan request

### Legacy Middleware

- `EnsureUserHasRole.php` — Sudah digantikan oleh `EnsureRole.php`. Tidak di-register lagi.

---

## 8. Layout & Blade Templates

### Struktur Layout

```
layouts/
├── app.blade.php          ← BASE LAYOUT (semua layout lain extends ini)
├── admin.blade.php        ← @extends('layouts.app') + sidebar admin
├── kasir.blade.php        ← @extends('layouts.app') + sidebar kasir
├── pelanggan.blade.php    ← @extends('layouts.app') + sidebar pelanggan
└── auth.blade.php         ← @extends('layouts.app') + hideSidebar=true
```

### app.blade.php — Base Layout

Memiliki 3 bagian utama:
1. **Navbar (`app-topbar`)** — Logo, user dropdown, hamburger menu (mobile)
2. **Sidebar (`app-sidebar`)** — Navigasi per role, di-`@yield('sidebar-menu')`
3. **Main Content (`app-main`)** — Alert messages, `@yield('content')`, footer

**Conditional Sidebar:**
- Jika `$hideSidebar` diset `true`, sidebar tidak ditampilkan (digunakan oleh halaman auth)
- CSS class `app-main--no-sidebar` diterapkan saat sidebar disembunyikan

**Slot yang Tersedia:**
- `@section('title')` — Judul halaman
- `@section('sidebar-menu')` — Menu sidebar berdasarkan role
- `@section('content')` — Konten utama halaman
- `@stack('scripts')` — Script tambahan yang di-push dari layout turunan
- `@yield('scripts')` — Script spesifik halaman

### Layout Turunan

Setiap layout role (admin, kasir, pelanggan) extends `layouts.app` dan menyediakan `@section('sidebar-menu')` dengan navigasi khusus role.

### Auth Blade Views

Terletak di `resources/views/auth/`:
- `login.blade.php`, `register.blade.php`, `reset-password.blade.php`, dll.
- Semua extends `layouts.auth` → `layouts.app` dengan `hideSidebar=true`
- Reset password tidak mandiri: halaman `/forgot-password` tidak ada. Admin membuat tautan dari `Manajemen User & Role` dan mengirimkannya manual ke user.

---

## 9. Blade Components

Terletak di `resources/views/components/`. Ini adalah **anonymous Blade components**.

### `<x-button>`

```blade
<x-button variant="primary" class="ms-2" :disabled="true">
    Simpan
</x-button>
```

| Props | Default | Opsi |
|---|---|---|
| `variant` | `'primary'` | `primary`, `secondary`, `success`, `warning`, `danger`, `outline`, `outline-secondary`, `brand` |
| `class` | `''` | CSS class tambahan |
| `disabled` | `false` | Boolean |

### `<x-card>`

```blade
<x-card class="mb-3">
    Konten card
</x-card>
```

| Props | Default |
|---|---|
| `class` | `''` |

### `<x-modal>`

```blade
<x-modal show="{{ $show }}" title="Konfirmasi" size="modal-lg">
    <p>Apakah Anda yakin?</p>
    <x-slot:footer>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
    </x-slot:footer>
</x-modal>
```

| Props | Default | Opsi |
|---|---|---|
| `show` | `false` | Boolean |
| `size` | `'modal-lg'` | `'modal-lg'`, `'modal-sm'` |
| `title` | `''` | String |

### `<x-badge>`

```blade
<x-badge status="pending" />
<x-badge status="selesai" />
<x-badge status="ditolak" />
```

| Props | Default | Opsi |
|---|---|---|
| `status` | `'pending'` | `pending`, `diproses`, `selesai`, `ditolak`, `menunggu` |
| `class` | `''` | CSS class tambahan |

**Status Mapping:**
| Status | CSS Class | Warna |
|---|---|---|
| `pending` / `menunggu` | `badge-soft--warning` | Kuning |
| `diproses` | `badge-soft--info` | Biru |
| `selesai` | `badge-soft--success` | Hijau |
| `ditolak` | `badge-soft--danger` | Merah |

### `<x-table>`

```blade
<x-table class="mb-3">
    <thead><tr><th>Nama</th></tr></thead>
    <tbody><tr><td>Data</td></tr></tbody>
</x-table>
```

| Props | Default |
|---|---|
| `class` | `''` |

---

## 10. Design System & CSS

### File CSS & Import Order

File utama: `resources/css/app.css`
```css
@import './bootstrap.css';
@import './variables.css';
@import './layout.css';
@import './components.css';
```

### `resources/css/variables.css`

CSS custom properties (`:root`) yang mendefinisikan seluruh design token:

**Color Palette:**
```css
--color-primary: #4F46E5;          /* Indigo */
--color-primary-dark: #4338CA;     /* Indigo dark */
--color-secondary: #64748B;        /* Slate */
--color-success: #10B981;          /* Emerald */
--color-warning: #F59E0B;          /* Amber */
--color-danger: #EF4444;           /* Red */
--color-info: #0EA5E9;             /* Sky blue */
--color-bg: #F8FAFC;               /* Background */
--color-surface: #FFFFFF;          /* Card/surface */
--color-border: #E2E8F0;           /* Border */
--color-text: #1E293B;             /* Primary text */
--color-text-muted: #94A3B8;       /* Muted text */
```

**Derived Tokens:**
```css
--color-primary-rgb: 79, 70, 229;
--color-primary-soft: #EEF2FF;
--color-sidebar-bg: #1E1B4B;
--color-sidebar-text: #C7D2FE;
```

**Layout Tokens (non-color):**
```css
--pos-radius: 0.5rem;
--pos-shadow-sm: ...;
--pos-shadow-md: ...;
--pos-font-sans: 'Inter', ...;
--pos-sidebar-width: 260px;
--pos-topbar-height: 64px;
```

### `resources/css/bootstrap.css`

```css
@import 'bootstrap/dist/css/bootstrap.min.css';
@import 'bootstrap-icons/font/bootstrap-icons.css';
```

### `resources/css/layout.css`

Berisi:
- `.app-main`, `.app-sidebar`, `.app-topbar`, `.app-page`, `.app-footer`
- `.app-sidebar__brand`, `.app-sidebar__nav`, `.app-sidebar__section-title`
- `.app-sidebar .nav-link` (hover, active states)
- `.app-topbar__title`, `.topbar-action`, `.topbar-action__badge`
- `.store-topbar`, `.store-nav`, `.store-content`, `.store-footer` (pelanggan shell)
- `.auth-shell`, `.auth-card` (auth pages)
- Responsive breakpoints (mobile sidebar toggle)

### `resources/css/components.css`

Berisi semua reusable component styles:
- `.btn-brand` — Primary brand button
- `.stat-card` + modifiers (`--primary`, `--success`, `--warning`, `--info`, `--neutral`)
- `.product-card` + `--out` (out of stock)
- `.pos-tile` + `--grid` variant
- `.table-wrap` — Card-style table with sticky header, zebra/hover rows
- `.badge-soft` + color variants (`--success`, `--warning`, `--danger`, `--info`, `--neutral`, `--count`)
- `.avatar` + size modifiers (`--sm`, `--lg`, `--dark`)
- `.order-card`
- `.receipt` — Thermal receipt style
- `.empty-state`
- `.pane` + `--flush`, `--tight`, `--accent`
- `.scroll-area` — Thin scrollbar
- `.note-box` + `--info`, `--warning`, `--success`
- `.checkout-stepper` + `.checkout-step`
- `.cart-item`, `.pos-screen`, `.pos-product-list`
- `.pay-success-icon`
- `.product-image--square`, `.thumb-sm`
- `.upload-preview`
- `.progress-bar--*` width modifiers

### Font

Menggunakan **Inter** dari Google Fonts. Didefinisikan di `variables.css`:
```css
--pos-font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif, ...;
```

### Transisi & Animasi

Semua interactive elements memiliki transisi 150-250ms:
- `transition: background-color 0.15s ease, color 0.15s ease` — sidebar nav links
- `transition: transform 0.15s ease, box-shadow 0.15s ease` — cards, stat cards
- `transition: border-color 0.15s ease, box-shadow 0.15s ease` — POS tiles

### Inline Style Dilarang

Semua CSS harus berada di file eksternal. Tidak boleh ada `style` attribute atau `<style>` tag di Blade views.

---

## 11. JavaScript & SweetAlert2

### File JS & Import Order

File utama: `resources/js/app.js`
```js
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import './sweetalert-config';
```

### SweetAlert2 Configuration

File: `resources/js/sweetalert-config.js`
```js
import Swal from 'sweetalert2';

Swal.mixin({
    customClass: {
        popup: 'rounded-4',
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-secondary',
    },
    buttonsStyling: false,
});
```

**Konfigurasi Global:**
- `popup` → `rounded-4` class (Bootstrap rounded)
- `confirmButton` → `btn btn-primary` (Bootstrap primary style)
- `cancelButton` → `btn btn-outline-secondary` (Bootstrap outline style)
- `buttonsStyling: false` → agar customClass diterapkan, bukan SweetAlert2 default styling

### Polling Notifikasi (Kasir)

Di `resources/views/layouts/kasir.blade.php`:
- `setInterval(checkNewOrders, 10000)` — Cek pesanan baru setiap 10 detik
- Menggunakan `axios` ke `/kasir/antrian/check-new`
- Menampilkan SweetAlert toast saat ada pesanan baru

### Bootstrap JS

Diimpor melalui `bootstrap/dist/js/bootstrap.bundle.min.js` dalam `app.js`. Vite mengelola bundling.

---

## 12. Controllers Overview

### Pola Controller

Semua controller extends `App\Http\Controllers\Controller` (abstract base). Menggunakan:
- PHP 8 constructor property promotion
- Explicit return type declarations
- Attribute-based Fillable (`#[Fillable([...])]`)
- Eloquent scopes dan query builder langsung di controller

### Admin Controllers

| Controller | Fungsi |
|---|---|
| `AdminDashboardController` | Dashboard statistik (users, products, orders, omzet) |
| `AdminUserController` | CRUD user & role management |
| `AdminProdukController` | CRUD produk |
| `AdminPromoController` | CRUD kode promo |

### Kasir Controllers

| Controller | Fungsi |
|---|---|
| `KasirDashboardController` | Dashboard kasir (penjualan hari ini, antrian) |
| `KasirPosController` | POS screen: keranjang di frontend, validasi promo, order |
| `KasirAntrianController` | Antrian pesanan: approve/reject |
| `KasirRiwayatController` | Riwayat transaksi |
| `KasirStrukController` | Cetak struk/invoice |
| `KasirLaporanHarianController` | Laporan harian |
| `KasirLaporanBulananController` | Laporan bulanan + PDF export |

### Pelanggan Controllers

| Controller | Fungsi |
|---|---|
| `PelangganKatalogController` | Katalog produk (filter, search, paginate) |
| `PelangganCartController` | Keranjang belanja (session-based) |
| `PelangganCheckoutController` | Checkout process |
| `PelangganRiwayatController` | Riwayat pesanan |

### Legacy Controllers (Deprecated)

- `CartController.php`, `CatalogController.php`, `OrderController.php` — Kontroler lawas yang sudah tidak digunakan

### Support Class

- `app/Support/NumberGenerator.php` — Generate unique numbers: `ORD-YYYYMMDD-NNN`, `TRX-YYYYMMDD-NNN`

---

## 13. Vite & Frontend Bundling

### Vite Configuration

File: `vite.config.js`
```js
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/bootstrap.css',
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
```

### Build Output

```
public/build/
├── manifest.json
├── assets/
│   ├── bootstrap-*.css
│   ├── bootstrap-icons-*.woff/woff2
│   ├── app-*.css
│   └── app-*.js
```

### Development Mode

```bash
npm run dev    # Vite dev server with HMR
```

### Production Build

```bash
npm run build  # Generate optimized assets in public/build/
```

### Laravel Blade Vite Directives

Dalam `app.blade.php`:
```blade
@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endif
```

---

## 14. Configuration Files

### composer.json

- **Laravel 13.17** + **PHP ^8.3**
- **barryvdh/laravel-dompdf** — PDF generation (laporan)
- **laravel/boost** — Laravel Boost MCP
- **laravel/breeze** — Auth scaffolding
- **laravel/pint** — Code formatter
- **fakerphp/faker** — Test data
- **mockery/mockery** — Mocking
- **phpunit/phpunit** — Testing

### package.json

- **bootstrap** ^5.3.8
- **bootstrap-icons** ^1.13.1
- **sweetalert2** ^11.26.25
- **@supabase/server** ^1.8.0
- **laravel-vite-plugin** ^3.1
- **vite** ^8.0.0
- **concurrently** ^10.0.3

### Key Laravel Config

- `config/app.php` — App name, locale, providers
- `config/database.php` — DB connections (sqlite, mysql, pgsql, mariadb)
- `bootstrap/app.php` — Routing, middleware aliases, exception handling

### Autoload PSR-4

```json
"App\\": "app/",
"App\\Support\\": "app/Support/",
"Database\\Factories\\": "database/factories/",
"Database\\Seeders\\": "database/seeders/"
```

---

## 15. Deployment

### Laravel Cloud

Laravel dapat dideploy menggunakan [Laravel Cloud](https://cloud.laravel.com).

```bash
# Aktifkan skill deploying-to-cloud jika deploy ke Laravel Cloud
```

### Local Development

```bash
php artisan serve
npm run dev
```

### Produksi

```bash
php artisan migrate --force
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan schedule:run
```

### Checklist Deployment

- [ ] `.env` sudah dikonfigurasi dengan benar (DB, APP_KEY, APP_URL)
- [ ] `php artisan migrate --force` sudah dijalankan
- [ ] `npm run build` sudah dijalankan
- [ ] `php artisan config:cache`, `route:cache`, `view:cache` sudah dijalankan
- [ ] `APP_DEBUG=false` di environment produksi
- [ ] `LOG_LEVEL=error` di environment produksi

---

## Appendix: CSS Class Quick Reference

### Badge Status

| Status | Class |
|---|---|
| Pending/Menunggu | `badge-soft badge-soft--warning` |
| Diproses | `badge-soft badge-soft--info` |
| Selesai | `badge-soft badge-soft--success` |
| Ditolak | `badge-soft badge-soft--danger` |

### Buttons

| Variant | Class |
|---|---|
| Primary | `btn btn-brand` |
| Secondary | `btn btn-secondary` |
| Success | `btn btn-success` |
| Warning | `btn btn-warning` |
| Danger | `btn btn-danger` |
| Outline | `btn btn-outline-primary` |

### Card

- `product-card` — Katalog produk
- `stat-card` + modifier — Dashboard statistik
- `order-card` — Daftar pesanan
- `pane` — Panel borderless
- `table-wrap` — Table wrapper dengan card style

### Layout

- `.app-main` — Main content wrapper
- `.app-sidebar` — Sidebar navigation
- `.app-topbar` — Top navigation bar
- `.app-page` — Page content area
- `.app-footer` — Footer
- `.app-main--no-sidebar` — Main without sidebar


### Responsive

- Mobile sidebar: `offcanvas-lg` (collapses below 992px)
- `.sidebar-toggle` — Hamburger menu button
- `.store-topbar` — Pelanggan navbar (no sidebar)
