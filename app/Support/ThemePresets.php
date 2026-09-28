<?php

namespace App\Support;

class ThemePresets
{
    public static function all(): array
    {
        return [
            'default' => [
                'name' => 'Default (Indigo)',
                'color_primary' => '#4F46E5',
                'color_primary_dark' => '#4338CA',
                'color_secondary' => '#64748B',
                'color_secondary_dark' => '#475569',
                'color_success' => '#10B981',
                'color_warning' => '#F59E0B',
                'color_danger' => '#EF4444',
            ],
            'ocean' => [
                'name' => 'Ocean (Teal)',
                'color_primary' => '#0D9488',
                'color_primary_dark' => '#0F766E',
                'color_secondary' => '#0891B2',
                'color_secondary_dark' => '#0E7490',
                'color_success' => '#10B981',
                'color_warning' => '#F59E0B',
                'color_danger' => '#EF4444',
            ],
            'forest' => [
                'name' => 'Forest (Emerald)',
                'color_primary' => '#059669',
                'color_primary_dark' => '#047857',
                'color_secondary' => '#16A34A',
                'color_secondary_dark' => '#15803D',
                'color_success' => '#22C55E',
                'color_warning' => '#F59E0B',
                'color_danger' => '#EF4444',
            ],
            'sunset' => [
                'name' => 'Sunset (Orange)',
                'color_primary' => '#EA580C',
                'color_primary_dark' => '#C2410C',
                'color_secondary' => '#F97316',
                'color_secondary_dark' => '#EA580C',
                'color_success' => '#10B981',
                'color_warning' => '#FBBF24',
                'color_danger' => '#EF4444',
            ],
            'midnight' => [
                'name' => 'Midnight (Dark Blue)',
                'color_primary' => '#1E3A8A',
                'color_primary_dark' => '#1E293B',
                'color_secondary' => '#3B82F6',
                'color_secondary_dark' => '#2563EB',
                'color_success' => '#10B981',
                'color_warning' => '#F59E0B',
                'color_danger' => '#EF4444',
            ],
            'rose' => [
                'name' => 'Rose (Pink)',
                'color_primary' => '#E11D48',
                'color_primary_dark' => '#BE123C',
                'color_secondary' => '#F43F5E',
                'color_secondary_dark' => '#E11D48',
                'color_success' => '#10B981',
                'color_warning' => '#F59E0B',
                'color_danger' => '#EF4444',
            ],
            'violet' => [
                'name' => 'Violet (Purple)',
                'color_primary' => '#7C3AED',
                'color_primary_dark' => '#6D28D9',
                'color_secondary' => '#A855F7',
                'color_secondary_dark' => '#9333EA',
                'color_success' => '#10B981',
                'color_warning' => '#F59E0B',
                'color_danger' => '#EF4444',
            ],
            'amber' => [
                'name' => 'Amber (Gold)',
                'color_primary' => '#D97706',
                'color_primary_dark' => '#B45309',
                'color_secondary' => '#F59E0B',
                'color_secondary_dark' => '#D97706',
                'color_success' => '#10B981',
                'color_warning' => '#FBBF24',
                'color_danger' => '#EF4444',
            ],
        ];
    }

    public static function get(string $variant): array
    {
        $presets = self::all();
        return $presets[$variant] ?? $presets['default'];
    }

    public static function applyToSettings(string $variant, \App\Models\Setting $setting): void
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