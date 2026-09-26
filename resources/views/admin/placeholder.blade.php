@extends('layouts.admin')

@section('title', $title . ' — Admin')
@section('page_title', $title)

@section('content')
    <div class="pane">
        <div class="empty-state">
            <i class="bi bi-tools empty-state__icon"></i>
            <h3 class="empty-state__title">Modul dalam pengembangan</h3>
            <p class="empty-state__text">Modul <strong>{{ $title }}</strong> sedang disiapkan. Silakan kembali lagi nanti.</p>
        </div>
    </div>
@endsection