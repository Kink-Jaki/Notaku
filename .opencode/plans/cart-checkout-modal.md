# Rencana: Keranjang + Checkout & Bayar via Modal (Pelanggan)

## Konteks / temuan di codebase

- Keranjang session-based (`pelanggan_cart`) **sudah ada**: `PelangganCartController` (add/update/remove/promo, semua AJAX JSON) + halaman `pelanggan/cart.blade.php` & `pelanggan/checkout.blade.php`.
- Ikon keranjang di navbar sekarang **dropdown Bootstrap** berisi list item + link ke halaman `/cart`. Badge tidak ter-update setelah add to cart (harus reload).
- **Bug yang harus diperbaiki agar fitur jalan**: di kartu produk (`marketplace.blade.php`) link detail memakai `.stretched-link` yang `::after`-nya `z-index: 1` (bootstrap.css), sedangkan tombol `btn` tidak `position: relative` → klik "Tambah ke Keranjang" tertimpa overlay dan jadi navigasi ke halaman detail. Perlu CSS `.product-card__body .btn { position: relative; z-index: 2; }`.
- Bootstrap 5 bundle + SweetAlert2 sudah diimport di `resources/js/app.js`; pola modal inline (`data-bs-toggle="modal"`) sudah dipakai di `kasir/antrian`, `pelanggan/riwayat` → ikuti pola itu.
- `orders` table tidak punya kolom phone/address/delivery_type (checkout lama hanya memvalidasi + memasukkan ke audit log) → **tanpa migrasi**, buy-now cukup parity dengan `store()`.
- `katalog.blade.php` adalah view mati (controller selalu return `pelanggan.marketplace`) → biarkan. `CartController`/`OrderController` legacy tidak di-route → biarkan.
- Test: PHPUnit (sqlite :memory:, `RefreshDatabase`, method `test_*` bahasa Indonesia), Playwright untuk E2E. Ada demo login `pelanggan@posapp.test` / `password`.

Keputusan user: (1) halaman `/cart` & `/checkout` **dihapus**, semua via modal; (2) setelah add to cart → **toast SweetAlert + badge navbar terupdate**, tombol keranjang = icon button (gaya hamburger) di topbar yang buka modal; (3) tombol **Beli Sekarang** di halaman detail **dan** kartu katalog.

---

## 1. Routes — `routes/web.php` (group `pelanggan.`)

**Hapus:**
- `GET /cart` → `pelanggan.cart`
- `GET /checkout` → `pelanggan.checkout`

**Tambah:**
- `GET /cart/modal` → `PelangganCartController@modal` (`pelanggan.cart.modal`) — return **HTML** isi body modal keranjang (untuk refresh setelah mutasi).
- `GET /checkout/summary` → `PelangganCheckoutController@summary` (`pelanggan.checkout.summary`) — return **HTML** panel item + ringkasan + promo. Query opsional `product_id` & `qty` (mode beli-sekarang), tanpa keduanya = dari session cart.
- `POST /checkout/buy-now` → `PelangganCheckoutController@buyNow` (`pelanggan.checkout.buyNow`).

**Tetap:** semua `POST /cart/*` dan `POST /checkout` (`checkout.store`).

Update tabel route di `DOCS.md` (baris ~350).

## 2. Backend

### `app/Http/Controllers/Pelanggan/PelangganCartController.php`
- Hapus `index()` (route dihapus).
- Tambah `modal(Request $request): View` → `view('pelanggan.partials.cart-modal')` (partial menghitung sendiri dari session; untuk guest cukup render empty-state login — guest tidak bisa mutasi cart).
- `update()`, `remove()`, `clear()`: tambahkan `cart_count` pada response JSON (add sudah punya) supaya badge bisa diupdate tanpa reload.
- Sisanya (promo etc.) tidak berubah.

### `app/Http/Controllers/Pelanggan/PelangganCheckoutController.php`
- Hapus `index()`.
- Tambah `summary(Request $request)`:
  - mode cart: baca session cart; kosong → 422 `{'error': 'Keranjang kosong'}`.
  - mode buy-now (`product_id`+`qty`): validasi product `is_active`, stok cukup → selain itu 404/400 JSON.
  - return `view('pelanggan.partials.checkout-summary', [...])`.
- Refactor: ekstrak logika pembuatan order di `store()` (promo re-check, `Order::create`, `OrderItem`, increment promo, `AuditLog`) menjadi **private `createOrder(array $items, Request $request): string`** (return `order_number`).
- `store()`: pakai `createOrder()`; tambah cabang JSON:
  - gagal validasi → `response()->json(['errors' => $validator->errors()], 422)` (non-JSON tetap `back()->withErrors()`).
  - sukses → `response()->json(['success' => true, 'order_number' => $no])`.
- Tambah `buyNow()`: auth-check JSON 401, validasi field checkout + `product_id`/`qty`, bangun `$items` dari product (1 produk), panggil `createOrder()` — **session cart tidak disentuh/dikosongkan**. Response JSON seperti `store()`.

## 3. Views

### Baru: `resources/views/pelanggan/partials/cart-modal.blade.php`
Self-contained (baca session cart + Product untuk gambar/kategori, diskon promo, total) — dipakai 2x: diinclude layout & di-return endpoint `cart.modal`. Isi:
- kosong → empty-state (guest: CTA "Masuk untuk Belanja").
- daftar item: thumb, nama, harga, stepper −/+ (`data-action="dec|inc"`), subtotal, tombol hapus.
- ringkasan subtotal/diskon/total.
- footer: `Lanjut Belanja` (tutup modal) + `Lanjut Checkout & Bayar` (hidden bila kosong).

### Baru: `resources/views/pelanggan/partials/checkout-summary.blade.php`
Self-contained: daftar item read-only (dari session cart **atau** dari `product_id`+`qty`), ringkasan subtotal/diskon/total, blok kode promo (apply/remove → `pelanggan.cart.promo` / `promoRemove` JSON).

### `resources/views/layouts/pelanggan.blade.php`
- Ganti **dua dropdown cart** (auth & guest) jadi **satu tombol ikon** `topbar-action` (`bi-cart3` + span badge `id="cartCountBadge"`) dengan `data-bs-toggle="modal" data-bs-target="#cartModal"` — posisi di samping tombol akun, gaya mirip sidebar-toggle.
- Tambah markup modal (setelah footer, sebelum `@endsection`):
  - `#cartModal` — `modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable`, header "Keranjang Belanja", body `#cartModalBody` berisi `@include('pelanggan.partials.cart-modal')`.
  - `#checkoutModal` — `modal-lg`, header "Checkout & Bayar", body = **form statis** (nama, no. WA, jenis pengiriman + field alamat yang di-toggle JS, catatan, metode pembayaran — dipindah dari `pelanggan/checkout.blade.php`) + `#checkoutSummary` (diisi via fetch) + hidden `mode`, `product_id`, `qty` + tombol `Bayar / Submit Pesanan`.
- `@push('scripts')` — JS bersama (delegated listener di `#cartModalBody`):
  - `refreshCartModal()` → fetch `route('pelanggan.cart.modal')` → `innerHTML` (hanya bila login).
  - `updateCartBadge(n)` dari `cart_count` tiap respons cart.
  - aksi stepper/hapus → fetch endpoint → refresh partial + badge (+ Swal toast hapus).
  - `openCheckout('cart')` → tutup cart modal, fetch `checkout.summary`, isi `#checkoutSummary`, buka `#checkoutModal`.
  - `openCheckout('buy_now', {productId, qty})` → set hidden fields, fetch `checkout.summary?product_id&qty`, isi, buka.
  - submit form via `fetch` (`Accept: application/json`): sukses → tutup modal, Swal sukses (no. pesanan), `window.location.href = route('marketplace')`; 422 → tandai `.is-invalid` + pesan per field; error lain → Swal error.
  - toggle alamat saat `delivery_type` berubah.

### `resources/views/pelanggan/marketplace.blade.php`
- Setelah add-to-cart sukses: toast (sudah ada) + **update badge** + refresh isi cart modal bila terbuka.
- Baru: tombol **Beli Sekarang** per kartu (flex bersama "Tambah"): guest → `requireLogin()`, login → `openCheckout('buy_now', {productId, qty: 1})`.
- `resources/views/pelanggan/produk.blade.php`: badge update pada add-to-cart; ganti link `<a href=checkout>` jadi tombol `openCheckout('buy_now', {productId, qty: #qtyInput})`.

### Hapus
- `resources/views/pelanggan/cart.blade.php`
- `resources/views/pelanggan/checkout.blade.php`

## 4. CSS — `resources/css/components.css`
```css
.product-card__body .btn { position: relative; z-index: 2; }
```
(agar tombol di atas overlay `.stretched-link`). Tambah style kecil bila perlu untuk list item modal (pakai ulang class `cart-row`, `order-summary`, `thumb-sm` yang sudah ada).

## 5. Tests

### PHPUnit (baru, `php artisan make:test --phpunit ...`)
- `PelangganCartTest`: add → session berisi + `cart_count`; guest → 401; stok kurang → 400; `GET cart/modal` → 200 & memuat nama produk; update/remove mengembalikan `cart_count`.
- `PelangganCheckoutTest`: store dari session cart → Order + OrderItems + cart kosong + JSON sukses; cart kosong → error; **buy-now membuat order tanpa mengubah session cart**; buy-now stok kurang → 400; validasi gagal → 422 `errors`; guest → 401; promo diskon tersimpan di order.
  (user test harus `email_verified_at` + role `pelanggan` karena middleware `verified`/`role`).

### Playwright (opsional, tambah `playwright/tests/client-cart.test.ts`)
Login `pelanggan@posapp.test` → klik "Tambah ke Keranjang" di kartu (assert **tidak** navigasi, badge naik) → buka modal keranjang → item muncul → "Lanjut Checkout & Bayar" → modal checkout → isi form → submit → pesanan `pending` muncul di DB/halaman riwayat.

## 6. Verifikasi (wajib sebelum selesai)
1. `vendor/bin/pint --dirty --format agent`
2. `php artisan test --compact` (catat baseline dulu — `RoleAccessTest` sudah tampak basi/route `/shop` tidak ada, jangan dianggap regresi baru)
3. `npm run build` (ada perubahan CSS/Blade → user perlu `composer run dev` bila pakai dev server)
4. Smoke manual: add to cart dari kartu (harus toast + badge naik, tidak pindah halaman), modal keranjang → checkout modal → submit, beli sekarang dari detail & kartu.

## Dampak / risiko
- Link/bookmark lama ke `/cart` & `/checkout` jadi 404 (dihapus sesuai permintaan).
- `CartController`/`OrderController` legacy tetap merujuk view yang dihapus — tidak berpengaruh karena tidak di-route.
- Diskon promo ditampilkan dari nilai session (`pelanggan_promo.discount`) yang dihitung saat promo di-apply; nilai selalu dihitung ulang oleh `createOrder()` sehingga order tetap benar (perbaikan tampilan ulang di partial opsional, di luar scope).
