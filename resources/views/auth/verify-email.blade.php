@extends('layouts.auth')

@section('title', 'Verifikasi Email — ' . \App\Support\SettingsHelper::get()->brand_name)

@section('auth_content')
    <div class="text-center mb-4">
        <span class="pay-success-icon d-inline-grid mb-3"><i class="bi bi-envelope-check"></i></span>
        <h1 class="h4 fw-semibold mb-1">Verifikasi Email Anda</h1>
        <p class="small text-muted-pos mb-0">
            Sebelum melanjutkan, cek email Anda untuk tautan verifikasi.
        </p>
    </div>

    <div class="d-grid gap-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-brand w-100 py-2">
                <i class="bi bi-send me-2"></i> Kirim Ulang Email Verifikasi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100 py-2">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </button>
        </form>
    </div>
@endsection