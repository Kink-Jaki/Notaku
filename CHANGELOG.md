# Changelog

Daftar perubahan penting pada aplikasi POS. Setiap commit baru otomatis menambahkan
entri baru di berkas ini melalui git hook `post-commit` (lihat `php artisan changelog:append`).

Format mengikuti [Keep a Changelog](https://keepachangelog.com/).

## [1.5.1] - 2026-10-07

### Changed
- update

## [1.5.0] - 2026-10-02

### Added
- Xendit payment, notifications, theming, and UI overhaul

## [1.4.2] - 2026-09-28

### Changed
- build production assets

## [1.4.1] - 2026-09-28

### Changed
- php lock 8.3

## [1.4.0] - 2026-09-28

**Personalization & Mobile UI**

### Added
- Menu admin **Developer** diganti menjadi **Personalization**.
- Sistem tema dengan 8 varian warna (Default, Ocean, Forest, Sunset, Midnight, Rose, Violet, Amber).
- Tambah warna secondary pada pengaturan tema.
- Tampilan mobile baru untuk Katalog, Keranjang, dan Checkout.
- Komponen *product-card* mobile.

## [1.3.1] - 2026-09-28

**Bug Fix Patch**

### Fixed
- Perbaikan konten & footer Marketplace yang ter-render ganda.
- Perbaikan error sintaks (*missing @endif*) pada layout utama.
- Perbaikan tes Playwright (selector, kredensial, modal).

## [1.3.0] - 2026-09-26

**Laporan & Antrian**

### Added
- Halaman Laporan Harian dengan filter tanggal & refetch AJAX.
- Antrian Pesanan kasir: approve / tolak dengan konfirmasi SweetAlert.
- Riwayat Transaksi dengan statistik & filter.
- Skeleton loading pada halaman kasir.
