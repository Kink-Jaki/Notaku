@extends('layouts.auth')

@section('title', 'Lupa Password — {{ $settings->brand_name }}')

@section('auth_content')
    <h1 class="h4 fw-semibold text-center mb-1">Lupa Password</h1>
    <p class="small text-muted-pos text-center mb-4">
        Masukkan email Anda dan kami akan mengirimkan tautan reset password.
    </p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
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

        <button type="submit" class="btn btn-brand w-100 py-2">
            <i class="bi bi-envelope-paper me-2"></i> Kirim Tautan Reset
        </button>
    </form>
@endsection

@section('auth_switch')
    <div class="auth-switch">
        <a class="fw-semibold" href="{{ route('login') }}">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
        </a>
    </div>
@endsection