@props(['size' => 'lg', 'category' => null, 'name' => null])

@php
    /**
     * Placeholder produk tanpa gambar. Setiap kategori mendapat ikon + gradient
     * sendiri supaya kartu tidak terlihat seragam kosong.
     *
     * Kunci diurutkan dari yang paling spesifik (`makanan & minuman`) ke yang
     * paling umum (`makanan`) karena pencocokan memakai str_contains pada
     * urutan array — kunci umum di awal akan selalu menang.
     */
    $petakan = [
        'makanan & minuman' => ['kelas' => 'ph-makanan', 'ikon' => 'bi-cup-hot', 'warna' => '#F97316'],
        'snack & flammables' => ['kelas' => 'ph-snack', 'ikon' => 'bi-cookie', 'warna' => '#EC4899'],
        'snack' => ['kelas' => 'ph-snack', 'ikon' => 'bi-cookie', 'warna' => '#EC4899'],
        'minuman' => ['kelas' => 'ph-minuman', 'ikon' => 'bi-cup-straw', 'warna' => '#0EA5E9'],
        'makanan' => ['kelas' => 'ph-makanan', 'ikon' => 'bi-egg-fried', 'warna' => '#F97316'],
        'sembako' => ['kelas' => 'ph-sembako', 'ikon' => 'bi-basket2', 'warna' => '#84CC16'],
        'bumbu' => ['kelas' => 'ph-sembako', 'ikon' => 'bi-mortarboard', 'warna' => '#84CC16'],
    ];

    $cocok = null;

    // Kategori adalah sinyal terkuat; nama produk dipakai sebagai cadangan
    // supaya kartu tanpa relasi kategori tetap mendapat warna yang relevan.
    foreach ([$category, $name] as $sumber) {
        $judul = mb_strtolower(trim((string) $sumber));

        if ($judul === '') {
            continue;
        }

        foreach ($petakan as $kunci => $meta) {
            if (str_contains($judul, $kunci)) {
                $cocok = $meta;
                break 2;
            }
        }
    }

    $cocok ??= ['kelas' => '', 'ikon' => 'bi-box-seam', 'warna' => '#64748B'];
    $judulTampil = trim((string) ($category ?? $name ?? '')) ?: 'Produk';
@endphp

<span
    {{ $attributes->merge(['class' => 'product-placeholder product-placeholder--'.$size.' '.$cocok['kelas']]) }}
    style="--ph-gradient: linear-gradient(135deg, {{ $cocok['warna'] }} 0%, {{ $cocok['warna'] }}cc 100%);"
    role="img"
    aria-label="Gambar belum tersedia untuk {{ $judulTampil }}"
>
    <i class="bi {{ $cocok['ikon'] }} product-placeholder__icon" aria-hidden="true"></i>
    @if ($size !== 'sm')
        <span class="product-placeholder__brand">{{ $judulTampil }}</span>
    @endif
</span>
