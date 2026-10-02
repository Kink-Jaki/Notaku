@php $settings = \App\Support\SettingsHelper::get(); @endphp

<footer class="app-footer app-footer--full" style="color: var(--color-footer-text);">
    <div class="footer-main">
        <div class="footer-col footer-col--brand">
            <div class="footer-brand">
                @if($settings->logo_path)
                    <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="{{ $settings->brand_name }}" class="footer-logo">
                @else
                    <i class="bi bi-bag" style="font-size: 1.5rem;"></i>
                @endif
                <span>{{ $settings->brand_name }}</span>
            </div>
            @if($settings->footer_tagline)
                <p class="footer-tagline" style="color: rgba(255,255,255,0.7);">{{ $settings->footer_tagline }}</p>
            @endif
            <div class="footer-social">
                @if($settings->social_instagram)
                    <a href="{{ $settings->social_instagram }}" target="_blank" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                @endif
                @if($settings->social_facebook)
                    <a href="{{ $settings->social_facebook }}" target="_blank" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                @endif
                @if($settings->social_tiktok)
                    <a href="{{ $settings->social_tiktok }}" target="_blank" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                @endif
                @if($settings->social_whatsapp)
                    <a href="https://wa.me/{{ $settings->social_whatsapp }}" target="_blank" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                @endif
            </div>
        </div>

        <div class="footer-col">
            <h6 class="footer-col__title" style="color: var(--color-footer-text);">Menu</h6>
            <ul class="footer-links">
                <li><a href="{{ route('marketplace') }}" style="color: rgba(255,255,255,0.8);">Katalog Produk</a></li>
                @auth
                    @if(auth()->user()->role === 'pelanggan')
                        <li><a href="{{ route('pelanggan.cart') }}" style="color: rgba(255,255,255,0.8);">Keranjang</a></li>
                        <li><a href="{{ route('pelanggan.pesanan-saya') }}" style="color: rgba(255,255,255,0.8);">Pesanan Saya</a></li>
                    @endif
                @else
                    <li><a href="{{ route('login') }}" style="color: rgba(255,255,255,0.8);">Masuk</a></li>
                    <li><a href="{{ route('register') }}" style="color: rgba(255,255,255,0.8);">Daftar</a></li>
                @endauth
                <li><a href="{{ route('pusat-bantuan') }}" style="color: rgba(255,255,255,0.8);">Pusat Bantuan</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h6 class="footer-col__title" style="color: var(--color-footer-text);">Bantuan</h6>
            <ul class="footer-links">
                <li><a href="{{ route('pusat-bantuan') }}#cara-memesan" style="color: rgba(255,255,255,0.8);">Cara Memesan</a></li>
                <li><a href="{{ route('pusat-bantuan') }}#barang-tidak-sesuai" style="color: rgba(255,255,255,0.8);">Barang Tidak Sesuai</a></li>
                <li><a href="{{ route('pusat-bantuan') }}#lupa-password" style="color: rgba(255,255,255,0.8);">Lupa Password</a></li>
                <li><a href="{{ route('pusat-bantuan') }}#hubungi-admin" style="color: rgba(255,255,255,0.8);">Hubungi Admin</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h6 class="footer-col__title" style="color: var(--color-footer-text);">Kontak</h6>
            <ul class="footer-links footer-links--contact">
                @if($settings->contact_address)
                    <li style="color: rgba(255,255,255,0.8);"><i class="bi bi-geo-alt"></i> {{ $settings->contact_address }}</li>
                @endif
                @if($settings->contact_phone)
                    <li style="color: rgba(255,255,255,0.8);"><i class="bi bi-telephone"></i> {{ $settings->contact_phone }}</li>
                @endif
                @if($settings->contact_email)
                    <li style="color: rgba(255,255,255,0.8);"><i class="bi bi-envelope"></i> {{ $settings->contact_email }}</li>
                @endif
            </ul>
        </div>
    </div>

    <div class="footer-bottom" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 16px; margin-top: 24px;">
        <span style="color: rgba(255,255,255,0.6);">&copy; {{ date('Y') }} {{ $settings->brand_name }}. Semua hak cipta dilindungi.</span>
    </div>
</footer>