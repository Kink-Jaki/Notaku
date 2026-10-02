@extends('layouts.app')

@php
    $hideFullFooter = true;
@endphp

@section('title', 'Admin — Notaku')

@section('sidebar-menu')
    @php
        $current = Route::currentRouteName();
        $adminNavMain = [
            ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'admin.dashboard', 'href' => route('admin.dashboard')],
            ['label' => 'Manajemen User & Role', 'icon' => 'bi-people', 'route' => 'admin.user-role', 'href' => route('admin.user-role.index')],
            ['label' => 'Kode Promo', 'icon' => 'bi-ticket-perforated', 'route' => 'admin.promo', 'href' => route('admin.promo.index')],
            ['label' => 'Kategori', 'icon' => 'bi-tags', 'route' => 'admin.kategori', 'href' => route('admin.kategori.index')],
            ['label' => 'Manajemen Produk', 'icon' => 'bi-box-seam', 'route' => 'admin.produk', 'href' => route('admin.produk.index')],
        ];
        $adminReports = [
            ['label' => 'Laporan Harian', 'icon' => 'bi-calendar-day', 'route' => 'admin.laporan-harian', 'href' => route('admin.laporan-harian')],
            ['label' => 'Laporan Bulanan', 'icon' => 'bi-calendar-month', 'route' => 'admin.laporan-bulanan', 'href' => route('admin.laporan-bulanan')],
        ];
        $settings = \App\Support\SettingsHelper::get();
    @endphp

    {{-- Brand --}}
    <div class="app-sidebar__brand">
        <span class="app-sidebar__brand-mark">
            @if ($settings->logo_path)
                <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="{{ $settings->brand_name }}" style="height: 28px; width: auto;">
            @else
                <i class="bi bi-bag"></i>
            @endif
        </span>
        <span class="app-sidebar__brand-text">{{ $settings->brand_name }}</span>
    </div>

    {{-- Collapse/Expand Button --}}
    <button type="button" class="app-sidebar__collapse-btn" id="sidebarCollapseBtn" aria-label="Perlebar sidebar" aria-expanded="false">
        <i class="bi bi-chevron-left" aria-hidden="true"></i>
        <span>Perlebar</span>
    </button>

    {{-- Navigation Sections --}}
    <nav class="app-sidebar__nav nav flex-column" aria-label="Menu utama">
    <div class="app-sidebar__section-title">Menu Utama</div>

    @foreach ($adminNavMain as $item)
        <a
            class="nav-link {{ $current === $item['route'] ? 'active' : '' }}"
            href="{{ $item['href'] }}"
            {{ $current === $item['route'] ? 'aria-current="page"' : '' }}
            data-tooltip="{{ $item['label'] }}"
        >
            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
            <span class="app-sidebar__nav-text">{{ $item['label'] }}</span>
        </a>
    @endforeach

    <div class="app-sidebar__section-title">Laporan</div>

    @foreach ($adminReports as $item)
        <a class="nav-link {{ $current === $item['route'] ? 'active' : '' }}" href="{{ $item['href'] }}" data-tooltip="{{ $item['label'] }}">
            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
            <span class="app-sidebar__nav-text">{{ $item['label'] }}</span>
        </a>
    @endforeach

    <div class="app-sidebar__section-title">Pengaturan</div>

    <a class="nav-link {{ $current === 'admin.developer' ? 'active' : '' }}" href="{{ route('admin.developer') }}" data-tooltip="Personalization">
        <i class="bi bi-palette" aria-hidden="true"></i>
        <span class="app-sidebar__nav-text">Personalization</span>
    </a>

    <div class="app-sidebar__section-title">Lainnya</div>

    @include('partials.nav-member')
    </nav>
@endsection
