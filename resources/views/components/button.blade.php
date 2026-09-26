@props(['variant' => 'primary', 'class' => '', 'disabled' => false])

@php
    $classes = match ($variant) {
        'primary' => 'btn btn-primary',
        'secondary' => 'btn btn-secondary',
        'success' => 'btn btn-success',
        'warning' => 'btn btn-warning',
        'danger' => 'btn btn-danger',
        'outline' => 'btn btn-outline-primary',
        'outline-secondary' => 'btn btn-outline-secondary',
        'brand' => 'btn btn-brand',
        default => 'btn btn-primary',
    };
@endphp

<button {{ $disabled ? 'disabled' : '' }} class="{{ $classes }} {{ $class }}">
    {{ $slot }}
</button>
