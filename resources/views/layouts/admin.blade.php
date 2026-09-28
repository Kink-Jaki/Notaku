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
    @endphp

    <div class="app-sidebar__section-title">Menu Utama</div>

    @foreach ($adminNavMain as $item)
        <a
            class="nav-link {{ $current === $item['route'] ? 'active' : '' }}"
            href="{{ $item['href'] }}"
            {{ $current === $item['route'] ? 'aria-current="page"' : '' }}
        >
            <i class="bi {{ $item['icon'] }}"></i>
            <span class="flex-grow-1">{{ $item['label'] }}</span>
        </a>
    @endforeach

    <div class="app-sidebar__section-title">Laporan</div>

    @foreach ($adminReports as $item)
        <a class="nav-link {{ $current === $item['route'] ? 'active' : '' }}" href="{{ $item['href'] }}">
            <i class="bi {{ $item['icon'] }}"></i>
            <span class="flex-grow-1">{{ $item['label'] }}</span>
        </a>
    @endforeach

    <div class="app-sidebar__section-title">Pengaturan</div>

    <a class="nav-link {{ $current === 'admin.developer' ? 'active' : '' }}" href="{{ route('admin.developer') }}">
        <i class="bi bi-palette"></i>
        <span class="flex-grow-1">Personalization</span>
    </a>

    <div class="app-sidebar__section-title">Lainnya</div>

    @include('partials.nav-member')
@endsection