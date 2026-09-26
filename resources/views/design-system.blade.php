<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Notaku — Design System</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/bootstrap.css', 'resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body>
        @php
            $stats = [
                ['label' => 'Penjualan Hari Ini', 'value' => 'Rp 2.450.000', 'icon' => 'bi-cash-stack', 'modifier' => 'primary', 'delta' => '+12,5%', 'deltaDir' => 'up'],
                ['label' => 'Total Pesanan', 'value' => '128', 'icon' => 'bi-receipt', 'modifier' => 'success', 'delta' => '+8,2%', 'deltaDir' => 'up'],
                ['label' => 'Pesanan Batal', 'value' => '3', 'icon' => 'bi-x-circle', 'modifier' => 'warning', 'delta' => '-2,1%', 'deltaDir' => 'down'],
            ];

            $products = [
                ['name' => 'Nasi Goreng Spesial', 'category' => 'Makanan', 'price' => 25000, 'icon' => 'bi-egg-fried'],
                ['name' => 'Es Teh Manis', 'category' => 'Minuman', 'price' => 8000, 'icon' => 'bi-cup-hot'],
                ['name' => 'Ayam Geprek', 'category' => 'Makanan', 'price' => 22000, 'icon' => 'bi-egg-fried'],
                ['name' => 'Indomie Goreng + Telur', 'category' => 'Makanan', 'price' => 15000, 'icon' => 'bi-basket'],
            ];

            $statusMap = [
                'menunggu' => 'badge-soft--warning',
                'diproses' => 'badge-soft--info',
                'selesai' => 'badge-soft--success',
                'ditolak' => 'badge-soft--danger',
            ];

            $orders = [
                ['no' => 'ORD-1001', 'customer' => 'Andi Saputra', 'items' => 3, 'total' => 53000, 'status' => 'menunggu', 'time' => '10:42'],
                ['no' => 'ORD-1002', 'customer' => 'Budi Wijaya', 'items' => 2, 'total' => 30000, 'status' => 'diproses', 'time' => '10:48'],
                ['no' => 'ORD-1003', 'customer' => 'Citra Lestari', 'items' => 5, 'total' => 112000, 'status' => 'selesai', 'time' => '10:21'],
                ['no' => 'ORD-1004', 'customer' => 'Dedi Kurnia', 'items' => 1, 'total' => 8000, 'status' => 'ditolak', 'time' => '10:05'],
            ];

            $receipt = [
                ['label' => 'Nasi Goreng Spesial', 'value' => 'Rp 25.000'],
                ['label' => 'Es Teh Manis x2', 'value' => 'Rp 16.000'],
                ['label' => 'Ayam Geprek', 'value' => 'Rp 22.000'],
            ];
        @endphp

        <main class="container-fluid py-4">
            {{-- Intro component sample --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <div>
                    <h1 class="h4 mb-1">Design System — Notaku</h1>
                    <p class="small text-muted-pos mb-0">Fondasi visual: token warna, shell, dan komponen reusable.</p>
                </div>
                <span class="badge-soft badge rounded-pill px-3 py-2">v1.0</span>
            </div>

            {{-- Buttons --}}
            <section class="mb-5">
                <h2 class="h5 mb-3 font-semibold">Tombol</h2>
                <div class="mb-3">
                    <button type="button" class="btn btn-primary">Primary</button>
                    <button type="button" class="btn btn-secondary">Secondary</button>
                    <button type="button" class="btn btn-success">Sukses</button>
                    <button type="button" class="btn btn-warning">Peringatan</button>
                    <button type="button" class="btn btn-danger">Bahaya</button>
                    <button type="button" class="btn btn-outline-primary">Outline</button>
                    <button type="button" class="btn btn-brand">Brand</button>
                    <button type="button" class="btn btn-outline-secondary">
                        <i class="bi bi-plus-lg me-1"></i> Tambah
                    </button>
                </div>
            </section>

            {{-- Stat cards --}}
            <section class="mb-5">
                <h2 class="h5 mb-3 font-semibold">Kartu Statistik</h2>
                <div class="row g-3">
                    @foreach ($stats as $stat)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="stat-card stat-card--{{ $stat['modifier'] }}">
                                <div>
                                    <div class="stat-card__label">{{ $stat['label'] }}</div>
                                    <div class="stat-card__value font-semibold">{{ $stat['value'] }}</div>
                                    <div class="stat-card__delta stat-card__delta--{{ $stat['deltaDir'] }}">
                                        <i class="bi bi-arrow-{{ $stat['deltaDir'] === 'up' ? 'up-right' : 'down-right' }}"></i>
                                        {{ $stat['delta'] }} dari kemarin
                                    </div>
                                </div>
                                <div class="stat-card__icon">
                                    <i class="bi {{ $stat['icon'] }}"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Product cards --}}
            <section class="mb-5">
                <h2 class="h5 mb-3 font-semibold">Katalog Produk</h2>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
                    @foreach ($products as $product)
                        <div class="col">
                            <div class="product-card">
                                <div class="product-card__image">
                                    <i class="bi {{ $product['icon'] }}"></i>
                                </div>
                                <div class="product-card__body">
                                    <div class="product-card__name">{{ $product['name'] }}</div>
                                    <div class="product-card__meta">
                                        <span class="badge-soft badge">{{ $product['category'] }}</span>
                                    </div>
                                    <div class="product-card__price">Rp {{ number_format($product['price'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Table + badges --}}
            <section class="mb-5">
                <h2 class="h5 mb-3 font-semibold">Daftar Pesanan</h2>
                <div class="table-wrap">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Jumlah Item</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td class="fw-semibold">{{ $order['no'] }}</td>
                                    <td>{{ $order['customer'] }}</td>
                                    <td>{{ $order['items'] }} item</td>
                                    <td>Rp {{ number_format($order['total'], 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge badge-soft {{ $statusMap[$order['status']] }}">
                                            <span class="badge-soft__dot"></span>
                                            {{ ucfirst($order['status']) }}
                                        </span>
                                    </td>
                                    <td class="text-muted-pos">{{ $order['time'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Avatar, order card, receipt --}}
            <section class="mb-5">
                <h2 class="h5 mb-3 font-semibold">Avatar & Order Card</h2>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="avatar">AS</span>
                    <span class="avatar avatar--sm">B</span>
                    <span class="avatar avatar--lg">CL</span>
                    <span class="avatar avatar--dark">DK</span>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        @foreach ($orders as $order)
                            <article class="order-card">
                                <div class="order-card__header">
                                    <h3 class="order-card__id h6">{{ $order['no'] }}</h3>
                                    <span class="order-card__time"><i class="bi bi-clock me-1"></i>{{ $order['time'] }}</span>
                                </div>
                                <div class="order-card__meta mb-1">
                                    <i class="bi bi-person"></i> {{ $order['customer'] }}
                                    <span class="mx-1">·</span>
                                    <i class="bi bi-box-seam"></i> {{ $order['items'] }} item
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-soft {{ $statusMap[$order['status']] }}">
                                        <span class="badge-soft__dot"></span>
                                        {{ ucfirst($order['status']) }}
                                    </span>
                                    <span class="order-card__total">Rp {{ number_format($order['total'], 0, ',', '.') }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="receipt">
                            <div class="receipt__title">Notaku</div>
                            <div class="receipt__store">Jl. Merdeka No. 12 — Kasir: Siti</div>
                            @foreach ($receipt as $line)
                                <div class="receipt-line">
                                    <span>{{ $line['label'] }}</span>
                                    <span>{{ $line['value'] }}</span>
                                </div>
                            @endforeach
                            <div class="receipt-line receipt-line--total">
                                <span>Total</span>
                                <span>Rp 63.000</span>
                            </div>
                            <div class="receipt-line receipt-line--label mt-1">
                                <span>Pajak</span>
                                <span>Termasuk</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Forms --}}
            <section class="mb-5">
                <h2 class="h5 mb-3 font-semibold">Formulir</h2>
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <div class="mb-3">
                            <label for="ds-name" class="form-label">Nama Lengkap</label>
                            <input id="ds-name" type="text" class="form-control" placeholder="Masukkan nama lengkap">
                        </div>
                        <div class="mb-3">
                            <label for="ds-role" class="form-label">Peran</label>
                            <select id="ds-role" class="form-select">
                                <option value="kasir">Kasir</option>
                                <option value="admin">Admin</option>
                                <option value="pelanggan">Pelanggan</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="mb-3">
                            <label for="ds-search" class="form-label">Cari Produk</label>
                            <div class="search-box">
                                <i class="bi bi-search search-box__icon"></i>
                                <input id="ds-search" type="search" class="form-control" placeholder="Cari nama produk atau kategori...">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="ds-catatan" class="form-label">Catatan</label>
                            <textarea id="ds-catatan" class="form-control" rows="3" placeholder="Catatan tambahan untuk pesanan..."></textarea>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Empty state --}}
            <section class="mb-5">
                <h2 class="h5 mb-3 font-semibold">Kosong</h2>
                <div class="pane">
                    <div class="empty-state">
                        <i class="bi bi-inbox empty-state__icon"></i>
                        <h3 class="empty-state__title">Belum ada pesanan</h3>
                        <p class="empty-state__text">Pesanan yang masuk akan tampil di sini. Gunakan tombol di bawah untuk membuat pesanan pertama.</p>
                        <button type="button" class="btn btn-brand mt-3">
                            <i class="bi bi-plus-lg me-1"></i> Buat Pesanan
                        </button>
                    </div>
                </div>
            </section>

            {{-- Modal (membuktikan Bootstrap JS aktif) --}}
            <section class="mb-3">
                <h2 class="h5 mb-3 font-semibold">Modal</h2>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#dsModal">
                    <i class="bi bi-window-stack me-1"></i> Buka Modal
                </button>

                <div id="dsModal" class="modal fade" tabindex="-1" aria-labelledby="dsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="dsModalLabel">Modal Bootstrap Aktif</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                Bootstrap JS terpasang dan berjalan melalui bundle yang diimpor dari <code>resources/js/app.js</code>.
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="button" class="btn btn-brand">Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>