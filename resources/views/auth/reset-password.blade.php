@extends('layouts.auth')

@section('title', 'Reset Password — ' . \App\Support\SettingsHelper::get()->brand_name)

@section('auth_content')
    <h1 class="h4 fw-semibold text-center mb-4">Atur Password Baru</h1>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input
                id="email"
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                name="email"
                value="{{ old('email', $request->email) }}"
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
            <label class="form-label" for="password">Password Baru</label>
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
                placeholder="Ulangi password baru"
            >
        </div>

        <button type="submit" class="btn btn-brand w-100 py-2">
            <i class="bi bi-shield-lock me-2"></i> Simpan Password Baru
        </button>
    </form>
@endsection