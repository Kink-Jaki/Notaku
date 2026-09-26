@extends('layouts.auth')

@section('title', 'Konfirmasi Password — {{ $settings->brand_name }}')

@section('auth_content')
    <h1 class="h4 fw-semibold text-center mb-1">Konfirmasi Password</h1>
    <p class="small text-muted-pos text-center mb-4">
        Konfirmasi password Anda sebelum melanjutkan, demi keamanan akun.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
            <label class="form-label" for="password">Password</label>
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

        <button type="submit" class="btn btn-brand w-100 py-2">
            <i class="bi bi-shield-check me-2"></i> Konfirmasi
        </button>
    </form>
@endsection