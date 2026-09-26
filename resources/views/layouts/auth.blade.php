@extends('layouts.app')
@php
    $hideSidebar = true;
    $settings = \App\Support\SettingsHelper::get();
@endphp

@section('title', 'Masuk — {{ $settings->brand_name }}')

@section('content')
    <div class="auth-shell">
        <div class="auth-shell__inner">
            <div class="auth-card">
                <div class="auth-card__brand">
                    @if ($settings->logo_path)
                        <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="{{ $settings->brand_name }}" style="height: 32px; width: auto;">
                    @else
                        <span class="app-sidebar__brand-mark"><i class="bi bi-bag"></i></span>
                    @endif
                    <span>{{ $settings->brand_name }}</span>
                </div>

                @yield('auth_content')
            </div>

            <div class="auth-switch">
                @yield('auth_switch')
            </div>
        </div>
    </div>
@endsection