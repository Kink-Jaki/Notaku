@props(['status' => 'pending', 'class' => ''])

@php
    $statusMap = [
        'pending' => 'badge-soft--warning',
        'diproses' => 'badge-soft--info',
        'selesai' => 'badge-soft--success',
        'ditolak' => 'badge-soft--danger',
        'menunggu' => 'badge-soft--warning',
        'diproses' => 'badge-soft--info',
    ];
    $variant = $statusMap[$status] ?? 'badge-soft--neutral';
@endphp

<span class="badge badge-soft {{ $variant }} {{ $class }}">
    <span class="badge-soft__dot"></span>
    {{ ucfirst($status) }}
</span>
