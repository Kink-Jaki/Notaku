@auth
    @if (Auth::user()->isPelanggan())
        <div
            class="dropdown"
            id="notifDropdown"
            data-notif-root
            data-list-url="{{ route('pelanggan.notifikasi.list') }}"
            data-read-url="{{ route('pelanggan.notifikasi.read') }}"
            data-fallback-url="{{ route('pelanggan.pesanan-saya') }}"
        >
            <button
                type="button"
                class="topbar-action dropdown-toggle"
                id="notifBell"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Notifikasi"
            >
                <i class="bi bi-bell"></i>
                <span class="topbar-action__badge d-none" data-notif-badge>0</span>
            </button>

            <div class="dropdown-menu dropdown-menu-end cart-menu">
                <div class="cart-menu__header">
                    <span class="cart-menu__title"><i class="bi bi-bell me-1"></i>Notifikasi</span>
                </div>

                <div class="cart-menu__list" data-notif-list></div>

                <div class="cart-menu__empty" data-notif-empty>
                    <div class="cart-menu__empty-icon"><i class="bi bi-bell-slash"></i></div>
                    <p class="cart-menu__empty-text">Belum ada notifikasi</p>
                </div>
            </div>
        </div>
    @endif
@endauth
