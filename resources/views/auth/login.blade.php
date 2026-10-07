@extends('layouts.auth')

@php
    $settings = \App\Support\SettingsHelper::get();
@endphp

@section('title', 'Login — ' . $settings->brand_name)

@section('auth_content')
    <h1 class="h4 fw-semibold text-center mb-1">Masuk ke Akun Anda</h1>
    <p class="small text-muted-pos text-center mb-4">Silakan masuk menggunakan email dan password Anda.</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input
                id="email"
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="nama@email.com"
            >
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0" for="password">Password</label>
                <button
                    type="button"
                    class="btn btn-link p-0 small fw-semibold text-decoration-none align-baseline"
                    data-bs-toggle="modal"
                    data-bs-target="#lupaPasswordModal .modal"
                >
                    Lupa password?
                </button>
            </div>
            <input
                id="password"
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
            >
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-4">
            <input id="remember_me" name="remember" type="checkbox" class="form-check-input">
            <label class="form-check-label small" for="remember_me">Ingat saya</label>
        </div>

        <button type="submit" class="btn btn-brand w-100 py-2">
            <i class="bi bi-box-arrow-in-right me-2"></i> Masuk
        </button>
    </form>

    {{-- ================= MODAL LUPA PASSWORD ================= --}}
    <div id="lupaPasswordModal">
        <x-modal size="modal-sm" title="Lupa Password">
            <p class="mb-3">Silahkan hubungi admin outlet untuk reset password akun Anda.</p>

            @if ($settings->social_whatsapp || $settings->contact_phone || $settings->contact_email)
                <ul class="list-unstyled mb-0">
                    @if ($settings->social_whatsapp)
                        <li class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-whatsapp text-success" aria-hidden="true"></i>
                            <span>
                                WhatsApp:
                                <a href="https://wa.me/{{ $settings->social_whatsapp }}" target="_blank" rel="noopener">
                                    {{ $settings->social_whatsapp }}
                                </a>
                            </span>
                        </li>
                    @endif
                    @if ($settings->contact_phone)
                        <li class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-telephone text-primary" aria-hidden="true"></i>
                            <span>Telepon: {{ $settings->contact_phone }}</span>
                        </li>
                    @endif
                    @if ($settings->contact_email)
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-envelope text-primary" aria-hidden="true"></i>
                            <span>Email: <a href="mailto:{{ $settings->contact_email }}">{{ $settings->contact_email }}</a></span>
                        </li>
                    @endif
                </ul>
            @else
                <p class="small text-muted-pos mb-0">Hubungi admin atau kasir di outlet tempat Anda mendaftar.</p>
            @endif
        </x-modal>
    </div>
@endsection

@section('auth_switch')
    <div class="auth-switch">
        Belum punya akun?
        <a class="fw-semibold" href="{{ route('register') }}">Daftar sekarang</a>
    </div>
@endsection