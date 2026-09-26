@extends('layouts.pelanggan')

@section('title', $title . ' — Pelanggan')

@section('content')
    <div class="pane">
        <div class="empty-state">
            <i class="bi bi-tools empty-state__icon"></i>
            <h3 class="empty-state__title">Modul dalam pengembangan</h3>
            <p class="empty-state__text">Modul <strong>{{ $title }}</strong> sedang disiapkan. Silakan kembali lagi nanti.</p>
            <a href="{{ route('marketplace') }}" class="btn btn-brand mt-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
            </a>
        </div>
    </div>
@endsection