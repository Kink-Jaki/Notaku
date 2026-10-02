<?php

use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProdukController;
use App\Http\Controllers\Admin\AdminPromoController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DeveloperController;
use App\Http\Controllers\Admin\LaporanBulananController;
use App\Http\Controllers\Admin\LaporanHarianController;
use App\Http\Controllers\Api\AdminDashboardApiController;
use App\Http\Controllers\Api\KasirAntrianApiController;
use App\Http\Controllers\Api\KasirDashboardApiController;
use App\Http\Controllers\Api\KasirLaporanHarianApiController;
use App\Http\Controllers\Api\KasirRiwayatApiController;
use App\Http\Controllers\Api\PelangganNotifikasiApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\HelpCenterController;
use App\Http\Controllers\Kasir\KasirAntrianController;
use App\Http\Controllers\Kasir\KasirDashboardController;
use App\Http\Controllers\Kasir\KasirLaporanBulananController;
use App\Http\Controllers\Kasir\KasirLaporanHarianController;
use App\Http\Controllers\Kasir\KasirPosController;
use App\Http\Controllers\Kasir\KasirRiwayatController;
use App\Http\Controllers\Kasir\KasirStrukController;
use App\Http\Controllers\Pelanggan\PelangganCartController;
use App\Http\Controllers\Pelanggan\PelangganCheckoutController;
use App\Http\Controllers\Pelanggan\PelangganKatalogController;
use App\Http\Controllers\Pelanggan\PelangganRiwayatController;
use App\Http\Controllers\Webhook\XenditWebhookController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PelangganKatalogController::class, 'index'])->name('pelanggan.katalog');
Route::get('/katalog', [PelangganKatalogController::class, 'index'])->name('pelanggan.katalog.index');
Route::get('/produk/{produk}', [PelangganKatalogController::class, 'show'])->name('pelanggan.produk');

Route::get('/', [PelangganKatalogController::class, 'index'])->name('marketplace');
Route::get('/produk/{produk}', [PelangganKatalogController::class, 'show'])->name('marketplace.produk');

Route::get('/api/produk', [ProductApiController::class, 'index'])->name('api.produk');

Route::get('/design-system', function () {
    return view('design-system');
})->name('design-system');

Route::get('/pusat-bantuan', [HelpCenterController::class, 'index'])->name('pusat-bantuan');

Route::get('/dashboard', function () {
    $user = Auth::user();

    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'kasir' => redirect()->route('kasir.dashboard'),
        default => redirect('/'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:pelanggan'])->name('pelanggan.')->group(function () {
    Route::get('/cart', [PelangganCartController::class, 'index'])->name('cart');
    Route::get('/cart/mobile', [PelangganCartController::class, 'indexMobile'])->name('cart.mobile');
    Route::post('/cart/add', [PelangganCartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [PelangganCartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [PelangganCartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [PelangganCartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/promo', [PelangganCartController::class, 'applyPromo'])->name('cart.promo');
    Route::post('/cart/promo/remove', [PelangganCartController::class, 'removePromo'])->name('cart.promoRemove');
    Route::get('/checkout', [PelangganCheckoutController::class, 'index'])->name('checkout');
    Route::get('/checkout/mobile', [PelangganCheckoutController::class, 'indexMobile'])->name('checkout.mobile');
    Route::post('/checkout', [PelangganCheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pesanan-saya', [PelangganRiwayatController::class, 'index'])->name('pesanan-saya');
    Route::put('/pesanan-saya/{order}/cancel', [PelangganRiwayatController::class, 'cancel'])->name('order.cancel');
    Route::get('/pesanan-saya/{order}/bayar', [PelangganRiwayatController::class, 'bayar'])->name('pembayaran.bayar');
    Route::get('/pesanan-saya/{order}/pembayaran', [PelangganRiwayatController::class, 'pembayaranStatus'])->name('pembayaran.status');
    Route::post('/pesanan-saya/{order}/pesan-lagi', [PelangganRiwayatController::class, 'pesanLagi'])->name('pesanan.pesan-lagi');
    Route::get('/katalog/mobile', [PelangganKatalogController::class, 'indexMobile'])->name('katalog.mobile');

    Route::get('/api/pelanggan/notifikasi', [PelangganNotifikasiApiController::class, 'index'])->name('notifikasi.list');
    Route::post('/api/pelanggan/notifikasi/read', [PelangganNotifikasiApiController::class, 'markAllRead'])->name('notifikasi.read');
});

Route::get('/api/kasir/dashboard', [KasirDashboardApiController::class, 'index'])
    ->middleware(['auth', 'role:kasir'])
    ->name('api.kasir.dashboard');

Route::get('/api/kasir/riwayat', [KasirRiwayatApiController::class, 'index'])
    ->middleware(['auth', 'role:kasir'])
    ->name('api.kasir.riwayat');

Route::get('/api/kasir/antrian', [KasirAntrianApiController::class, 'index'])
    ->middleware(['auth', 'role:kasir'])
    ->name('api.kasir.antrian');

Route::get('/api/kasir/laporan-harian', [KasirLaporanHarianApiController::class, 'index'])
    ->middleware(['auth', 'role:kasir'])
    ->name('api.kasir.laporan-harian');

Route::middleware(['auth', 'verified', 'role:kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/dashboard', [KasirDashboardController::class, 'index'])->name('dashboard');
    Route::get('/pos', [KasirPosController::class, 'index'])->name('pos');
    Route::post('/pos/promo/validate', [KasirPosController::class, 'validatePromo'])->name('pos.promoValidate');
    Route::post('/pos/order', [KasirPosController::class, 'order'])->name('pos.order');

    Route::get('/antrian', [KasirAntrianController::class, 'index'])->name('antrian');
    Route::post('/antrian/{order}/approve', [KasirAntrianController::class, 'approve'])->name('antrian.approve');
    Route::post('/antrian/{order}/reject', [KasirAntrianController::class, 'reject'])->name('antrian.reject');
    Route::post('/antrian/{order}/complete', [KasirAntrianController::class, 'complete'])->name('antrian.complete');

    Route::get('/antrian/check-new', [KasirAntrianController::class, 'checkNewOrders'])->name('antrian.check-new');

    Route::get('/riwayat', [KasirRiwayatController::class, 'index'])->name('riwayat');

    Route::get('/transaksi/{id}', [KasirStrukController::class, 'show'])->name('transaksi');

    Route::get('/laporan-harian', [KasirLaporanHarianController::class, 'index'])->name('laporan-harian');

    Route::get('/laporan-bulanan', [KasirLaporanBulananController::class, 'index'])->name('laporan-bulanan');
    Route::get('/laporan-bulanan/export', [KasirLaporanBulananController::class, 'export'])->name('laporan-bulanan.export');
});

Route::get('/api/admin/dashboard', [AdminDashboardApiController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('api.admin.dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('user-role', AdminUserController::class)->except(['create', 'edit', 'show']);
    Route::resource('promo', AdminPromoController::class)->except(['create', 'edit', 'show']);
    Route::resource('produk', AdminProdukController::class)->except(['create', 'edit', 'show']);
    Route::resource('kategori', AdminCategoryController::class)->except(['create', 'edit', 'show']);

    Route::get('/laporan-harian', [LaporanHarianController::class, 'index'])->name('laporan-harian');
    Route::get('/laporan-bulanan', [LaporanBulananController::class, 'index'])->name('laporan-bulanan');

    Route::get('/developer', [DeveloperController::class, 'index'])->name('developer');
    Route::post('/developer', [DeveloperController::class, 'update'])->name('developer.update');
});

require __DIR__.'/auth.php';

Route::post('/webhooks/xendit', XenditWebhookController::class)->name('webhook.xendit');
