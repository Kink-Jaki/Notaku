@extends('layouts.auth')

@section('title', 'Daftar — {{ $settings->brand_name }}')

@section('auth_content')
    <h1 class="h4 fw-semibold text-center mb-1">Buat Akun Baru</h1>
    <p class="small text-muted-pos text-center mb-4">Daftar sebagai pelanggan untuk mulai berbelanja.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="name">Nama Lengkap</label>
            <input
                id="name"
                type="text"
                class="form-control @error('name') is-invalid @enderror"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Nama Anda"
            >
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input
                id="email"
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                placeholder="nama@email.com"
            >
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input
                id="password"
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Minimal 8 karakter"
            >
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
            <input
                id="password_confirmation"
                type="password"
                class="form-control"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Ulangi password"
            >
        </div>

        <button type="submit" class="btn btn-brand w-100 py-2">
            <i class="bi bi-person-plus me-2"></i> Daftar
        </button>
    </form>
@endsection

@section('auth_switch')
    <div class="auth-switch">
        Sudah punya akun?
        <a class="fw-semibold" href="{{ route('login') }}">Masuk</a>
    </div>
@endsection