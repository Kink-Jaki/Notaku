<?php

namespace App\Support;

use App\Models\Setting;

class ThemePresets
{
    /**
     * Warna dasar tiap varian.
     *
     * `color_primary` menentukan navigasi, tombol utama, dan sidebar.
     * `color_secondary` dipakai sebagai slot *accent* di desain baru — warna
     * CTA kucing-toko seperti tombol "Tambah Keranjang" — jadi setiap varian
     * harus punya pasangan primary/accent yang kontras dan tetap terbaca
     * dengan teks putih.
     *
     * Key `surfaces` menyimpan warna netral (latar, border, teks) yang
     * diturunkan per varian supaya tidak perlu ditulis manual di Blade.
     *
     * @var array<string, array<string, mixed>>
     */
    private const PALETTES = [
        'default' => [
            'name' => 'Default (Indigo)',
            'color_primary' => '#4F46E5',
            'color_primary_dark' => '#4338CA',
            'color_secondary' => '#F97316',
            'color_secondary_dark' => '#EA580C',
            'color_success' => '#10B981',
            'color_warning' => '#F59E0B',
            'color_danger' => '#EF4444',
            'surfaces' => [
                'color_bg' => '#F8FAFC',
                'color_surface' => '#FFFFFF',
                'color_border' => '#E2E8F0',
                'color_text' => '#1E293B',
                'color_text_muted' => '#94A3B8',
            ],
        ],
        'ocean' => [
            'name' => 'Ocean (Teal)',
            'color_primary' => '#0D9488',
            'color_primary_dark' => '#0F766E',
            'color_secondary' => '#F59E0B',
            'color_secondary_dark' => '#D97706',
            'color_success' => '#10B981',
            'color_warning' => '#F59E0B',
            'color_danger' => '#EF4444',
            'surfaces' => [
                'color_bg' => '#F0FDFA',
                'color_surface' => '#FFFFFF',
                'color_border' => '#CCFBF1',
                'color_text' => '#134E4A',
                'color_text_muted' => '#4B9B8E',
            ],
        ],
        'forest' => [
            'name' => 'Forest (Emerald)',
            'color_primary' => '#059669',
            'color_primary_dark' => '#047857',
            'color_secondary' => '#F97316',
            'color_secondary_dark' => '#EA580C',
            'color_success' => '#22C55E',
            'color_warning' => '#F59E0B',
            'color_danger' => '#EF4444',
            'surfaces' => [
                'color_bg' => '#F0FDF4',
                'color_surface' => '#FFFFFF',
                'color_border' => '#DCFCE7',
                'color_text' => '#14532D',
                'color_text_muted' => '#4ADE80',
            ],
        ],
        'sunset' => [
            'name' => 'Sunset (Amber)',
            'color_primary' => '#B45309',
            'color_primary_dark' => '#92400E',
            'color_secondary' => '#0EA5E9',
            'color_secondary_dark' => '#0284C7',
            'color_success' => '#10B981',
            'color_warning' => '#FBBF24',
            'color_danger' => '#EF4444',
            'surfaces' => [
                'color_bg' => '#FFFBEB',
                'color_surface' => '#FFFFFF',
                'color_border' => '#FDE68A',
                'color_text' => '#78350F',
                'color_text_muted' => '#D97706',
            ],
        ],
        'midnight' => [
            'name' => 'Midnight (Navy)',
            'color_primary' => '#1E3A8A',
            'color_primary_dark' => '#1E293B',
            'color_secondary' => '#6366F1',
            'color_secondary_dark' => '#4F46E5',
            'color_success' => '#10B981',
            'color_warning' => '#F59E0B',
            'color_danger' => '#EF4444',
            'surfaces' => [
                'color_bg' => '#0F172A',
                'color_surface' => '#1E293B',
                'color_border' => '#334155',
                'color_text' => '#F1F5F9',
                'color_text_muted' => '#94A3B8',
            ],
        ],
        'rose' => [
            'name' => 'Rose (Pink)',
            'color_primary' => '#E11D48',
            'color_primary_dark' => '#BE123C',
            'color_secondary' => '#7C3AED',
            'color_secondary_dark' => '#6D28D9',
            'color_success' => '#10B981',
            'color_warning' => '#F59E0B',
            'color_danger' => '#EF4444',
            'surfaces' => [
                'color_bg' => '#FFF1F2',
                'color_surface' => '#FFFFFF',
                'color_border' => '#FFE4E6',
                'color_text' => '#881337',
                'color_text_muted' => '#FB7185',
            ],
        ],
        'violet' => [
            'name' => 'Violet (Purple)',
            'color_primary' => '#7C3AED',
            'color_primary_dark' => '#6D28D9',
            'color_secondary' => '#F43F5E',
            'color_secondary_dark' => '#E11D48',
            'color_success' => '#10B981',
            'color_warning' => '#F59E0B',
            'color_danger' => '#EF4444',
            'surfaces' => [
                'color_bg' => '#FAF5FF',
                'color_surface' => '#FFFFFF',
                'color_border' => '#F3E8FF',
                'color_text' => '#4C1D95',
                'color_text_muted' => '#C084FC',
            ],
        ],
        'amber' => [
            'name' => 'Amber (Gold)',
            'color_primary' => '#D97706',
            'color_primary_dark' => '#B45309',
            'color_secondary' => '#0F766E',
            'color_secondary_dark' => '#115E59',
            'color_success' => '#10B981',
            'color_warning' => '#FBBF24',
            'color_danger' => '#EF4444',
            'surfaces' => [
                'color_bg' => '#FFFBEB',
                'color_surface' => '#FFFFFF',
                'color_border' => '#FDE68A',
                'color_text' => '#78350F',
                'color_text_muted' => '#D97706',
            ],
        ],
    ];

    public static function all(): array
    {
        return self::PALETTES;
    }

    public static function get(string $variant): array
    {
        $presets = self::all();

        return $presets[$variant] ?? $presets['default'];
    }

    /**
     * Warna netral (latar, border, teks) untuk sebuah varian.
     *
     * @return array<string, string>
     */
    public static function surfaces(string $variant): array
    {
        return self::get($variant)['surfaces'] ?? self::get('default')['surfaces'];
    }

    public static function applyToSettings(string $variant, Setting $setting): void
    {
        $preset = self::get($variant);
        $setting->color_primary = $preset['color_primary'];
        $setting->color_primary_dark = $preset['color_primary_dark'];
        $setting->color_secondary = $preset['color_secondary'];
        $setting->color_secondary_dark = $preset['color_secondary_dark'];
        $setting->color_success = $preset['color_success'];
        $setting->color_warning = $preset['color_warning'];
        $setting->color_danger = $preset['color_danger'];
    }

    public static function getVariantOptions(): array
    {
        $options = [];
        foreach (self::all() as $key => $preset) {
            $options[$key] = $preset['name'];
        }

        return $options;
    }
}
