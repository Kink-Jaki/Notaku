@extends('layouts.auth')

@section('title', 'Login — {{ $settings->brand_name }}')

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
                @if (Route::has('password.request'))
                    <a class="small fw-semibold" href="{{ route('password.request') }}">Lupa password?</a>
                @endif
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

    <div class="auth-card__foot">
        <div class="d-flex justify-content-around text-capitalize flex-wrap gap-2">
            <span><i class="bi bi-person-badge me-1"></i>admin@posapp.test</span>
            <span>password</span>
        </div>
        <div class="text-muted-pos mt-2">Akun demo untuk semua role (admin / kasir / pelanggan)</div>
    </div>
@endsection

@section('auth_switch')
    <div class="auth-switch">
        Belum punya akun?
        <a class="fw-semibold" href="{{ route('register') }}">Daftar sekarang</a>
    </div>
@endsection