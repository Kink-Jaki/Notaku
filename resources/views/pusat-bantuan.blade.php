@extends('layouts.app')

@section('title', 'Pusat Bantuan — Notaku')

@section('sidebar-menu')
    <div class="app-sidebar__section-title">Bantuan</div>

    <a class="nav-link active" href="{{ route('pusat-bantuan') }}" aria-current="page">
        <i class="bi bi-question-circle"></i>
        <span class="flex-grow-1">Pusat Bantuan</span>
    </a>

    <a class="nav-link" href="{{ route('dashboard') }}">
        <i class="bi bi-arrow-left"></i>
        <span class="flex-grow-1">Kembali</span>
    </a>
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-1">Pusat Bantuan</h1>
            <p class="small text-muted-pos mb-0">Panduan pesanan, produk, dan akun untuk pelanggan, kasir, dan admin.</p>
        </div>
        <span class="badge-soft badge rounded-pill px-3 py-2"><i class="bi bi-lightbulb me-1"></i> FAQ</span>
    </div>

    <div class="pane p-3 p-md-4">
        <div class="accordion" id="pusatBantuanAccordion">
            {{-- ================= CHANGELOG & UPDATE PATCH ================= --}}
            <div class="accordion-item" id="changelog">
                <h2 class="accordion-header">
                    <button
                        class="accordion-button"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseChangelog"
                        aria-expanded="true"
                        aria-controls="collapseChangelog"
                    >
                        <i class="bi bi-journal-text me-2"></i> Changelog &amp; Update Patch
                    </button>
                </h2>
                <div id="collapseChangelog" class="accordion-collapse collapse show" data-bs-parent="#pusatBantuanAccordion">
                    <div class="accordion-body">
                        <div class="timeline">
                            @php $changelogEntries = \App\Support\Changelog::entries(); @endphp

                            @forelse ($changelogEntries as $entry)
                                <div class="timeline-item">
                                    <div class="d-flex align-items-start gap-3 {{ $loop->last ? '' : 'mb-4' }}">
                                        <span class="badge badge-soft badge-soft--{{ $entry['badge'] }} flex-shrink-0">{{ 'v' . ltrim($entry['version'], 'v') }}</span>
                                        <div class="flex-grow-1">
                                            @if ($entry['title'] !== null)
                                                <div class="fw-semibold">{!! \App\Support\Changelog::inline($entry['title']) !!}</div>
                                            @endif
                                            @if ($entry['date'] !== '')
                                                <div class="small text-muted-pos mb-2">{{ \App\Support\Changelog::formatDate($entry['date']) }}</div>
                                            @endif
                                            @foreach ($entry['sections'] as $section)
                                                <div class="mb-2">
                                                    <span class="badge badge-soft badge-soft--{{ \App\Support\Changelog::sectionBadge($section['type']) }}">{{ $section['type'] }}</span>
                                                    <ul class="small mb-0 ps-3 mt-1">
                                                        @foreach ($section['items'] as $item)
                                                            <li>{!! \App\Support\Changelog::inline($item) !!}</li>
                                                    @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="small text-muted-pos mb-0">Belum ada perubahan yang dicatat.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= CARA MEMESAN ================= --}}
            <div class="accordion-item" id="cara-memesan">
                <h2 class="accordion-header">
                    <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseCaraMemesan"
                        aria-expanded="false"
                        aria-controls="collapseCaraMemesan"
                    >
                        Bagaimana cara memesan produk?
                    </button>
                </h2>
                <div id="collapseCaraMemesan" class="accordion-collapse collapse" data-bs-parent="#pusatBantuanAccordion">
                    <div class="accordion-body">
                        <p class="mb-3">Alur pemesanan di aplikasi ini:</p>
                        <ol class="mb-3">
                            <li>Buka <strong>Katalog</strong> (halaman utama / menu sidebar) untuk melihat daftar produk yang tersedia.</li>
                            <li>Pilih produk, atur jumlahnya, lalu klik <strong>Tambah ke Keranjang</strong>.</li>
                            <li>Buka <strong>Keranjang</strong>, periksa kembali item dan koden promo, lalu klik <strong>Checkout</strong>.</li>
                            <li>Isi data pesanan (nama penerima, no. WhatsApp, jenis pengiriman) dan klik <strong>Kirim Pesanan</strong>.</li>
                            <li>Pesanan berstatus <strong>Menunggu</strong> dan masuk ke antrian kasir. Tunggu kasir menyetujui pesanan sebelum diproses.</li>
                            <li>Pantau statusnya kapan saja di menu <strong>Pesanan Saya</strong>.</li>
                        </ol>
                        <p class="small text-muted-pos mb-0">Pemesanan hanya bisa dilakukan oleh pelanggan yang sudah login. Belum punya akun? Daftar dulu dari halaman Login.</p>
                    </div>
                </div>
            </div>

            {{-- ================= BARANG TIDAK SESUAI ================= --}}
            <div class="accordion-item" id="barang-tidak-sesuai">
                <h2 class="accordion-header">
                    <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseBarangTidakSesuai"
                        aria-expanded="false"
                        aria-controls="collapseBarangTidakSesuai"
                    >
                        Barang tidak sesuai?
                    </button>
                </h2>
                <div id="collapseBarangTidakSesuai" class="accordion-collapse collapse" data-bs-parent="#pusatBantuanAccordion">
                    <div class="accordion-body">
                        <p class="mb-3">
                            POS &amp; Order melayani pesanan pada <strong>satu outlet (order &amp; ambil di tempat)</strong>, bukan e-commerce
                            dengan pengiriman. Karena itu penanganan produk yang tidak sesuai dilakukan <strong>langsung di outlet</strong>:
                        </p>
                        <ol class="mb-3">
                            <li>Segera hubungi kasir / admin di outlet saat itu juga (atau lewat WhatsApp outlet).</li>
                            <li>Sertakan <strong>nomor pesanan / struk</strong> agar pesanan mudah ditemukan.</li>
                            <li>Bawa atau tunjukkan produk yang tidak sesuai untuk dicek ulang oleh kasir.</li>
                            <li>Kasir akan memproses penggantian atau penyesuaian pesanan sesuai kebijakan outlet.</li>
                        </ol>
                        <p class="small text-muted-pos mb-0">Pesanan yang sedang berjalan bisa dicek statusnya di <strong>Pesanan Saya</strong> (pelanggan) atau menu <strong>Antrian Pesanan</strong> (kasir).</p>
                    </div>
                </div>
            </div>

            {{-- ================= LUPA PASSWORD ================= --}}
            <div class="accordion-item" id="lupa-password">
                <h2 class="accordion-header">
                    <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseLupaPassword"
                        aria-expanded="false"
                        aria-controls="collapseLupaPassword"
                    >
                        Lupa password?
                    </button>
                </h2>
                <div id="collapseLupaPassword" class="accordion-collapse collapse" data-bs-parent="#pusatBantuanAccordion">
                    <div class="accordion-body">
                        <p class="mb-3">Gunakan fitur <strong>Lupa password?</strong> yang tersedia di halaman Login:</p>
                        <ol class="mb-3">
                            <li>Buka halaman Login, lalu klik tautan <strong>Lupa password?</strong> (atau langsung ke <code>/forgot-password</code>).</li>
                            <li>Masukkan alamat email yang terdaftar, lalu kirim tautan reset password.</li>
                            <li>Buka email masuk dan klik tautan <strong>Reset Password</strong> yang dikirim sistem.</li>
                            <li>Masukkan password baru, lalu login kembali dengan password terbaru.</li>
                        </ol>
                        <a href="{{ route('password.request') }}" class="btn btn-brand btn-sm">
                            <i class="bi bi-key me-1"></i> Reset Password Sekarang
                        </a>
                        <p class="small text-muted-pos mt-3 mb-0">Email reset tidak masuk? Cek folder <strong>Spam</strong> atau hubungi admin outlet.</p>
                    </div>
                </div>
            </div>

            {{-- ================= HUBUNGI ADMIN ================= --}}
            <div class="accordion-item" id="hubungi-admin">
                <h2 class="accordion-header">
                    <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseHubungiAdmin"
                        aria-expanded="false"
                        aria-controls="collapseHubungiAdmin"
                    >
                        Hubungi admin
                    </button>
                </h2>
                <div id="collapseHubungiAdmin" class="accordion-collapse collapse" data-bs-parent="#pusatBantuanAccordion">
                    <div class="accordion-body">
                        <p class="mb-3">Butuh bantuan lebih lanjut? Hubungi admin / kasir outlet melalui kontak berikut:</p>
                        <ul class="list-unstyled mb-3">
                            <li class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-whatsapp text-success"></i>
                                <span>WhatsApp: <a href="https://wa.me/6281234567890" target="_blank" rel="noopener">+62 812-3456-7890</a></span>
                            </li>
                            <li class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-envelope text-primary"></i>
                                <span>Email: <a href="mailto:halo@posorder.test">halo@posorder.test</a></span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-geo-alt text-danger"></i>
                                <span>Outlet: Jl. Contoh Alamat No. 1 (senin–minggu, 08.00–21.00)</span>
                            </li>
                        </ul>
                        <p class="small text-muted-pos mb-0">Nomor dan alamat di atas masih placeholder — silakan ganti dengan kontak outlet yang sebenarnya.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
