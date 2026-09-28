@props(['size' => 'lg'])

@php
    $settings = \App\Support\SettingsHelper::get();
    $brandName = $settings->brand_name ?: config('app.name');
    $brandLogo = $settings->logo_path ? asset('storage/' . $settings->logo_path) : null;
@endphp

<span {{ $attributes->merge([
    'class' => "product-placeholder product-placeholder--{$size}",
    'title' => $brandName,
    'role' => 'img',
    'aria-label' => "Gambar produk {$brandName}",
]) }}>
    @if ($brandLogo)
        <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="product-placeholder__logo" loading="lazy">
    @else
        <i class="bi bi-box-seam product-placeholder__icon" aria-hidden="true"></i>
    @endif
    <span class="product-placeholder__brand">{{ $brandName }}</span>
</span>
