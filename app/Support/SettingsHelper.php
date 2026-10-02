<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    public static function get(): Setting
    {
        $data = Cache::rememberForever('app_settings', function () {
            $setting = Setting::first();

            return $setting ? $setting->toArray() : [];
        });

        return new Setting($data);
    }

    /**
     * Ubah hex (#RGB / #RRGGBB) menjadi triplet "r, g, b" untuk dipakai di rgba().
     */
    public static function hexToRgb(?string $hex): string
    {
        return implode(', ', self::toRgbChannels($hex, '#4F46E5'));
    }

    /**
     * Campur hex dengan warna tujuan. $amount adalah porsi warna asal (0-1).
     * Dipakai untuk menurunkan shade hover/active/soft dari satu warna dasar.
     */
    public static function mix(?string $hex, string $target, float $amount): string
    {
        $channels = self::toRgbChannels($hex, '#4F46E5');
        $targetChannels = self::toRgbChannels($target, '#000000');

        $amount = max(0.0, min(1.0, $amount));

        $mixed = array_map(
            fn (int $from, int $to) => (int) round($from * $amount + $to * (1 - $amount)),
            $channels,
            $targetChannels,
        );

        return sprintf('#%02X%02X%02X', ...$mixed);
    }

    /**
     * Pecah hex menjadi tiga channel 0-255, dengan fallback bila formatnya
     * tidak valid (mis. kosong atau bukan hex).
     *
     * @return array{int, int, int}
     */
    private static function toRgbChannels(?string $hex, string $fallback): array
    {
        $hex = ltrim(trim((string) $hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            $hex = ltrim($fallback, '#');
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Turunkan token warna brand + semantik dari warna yang dipilih di
     * halaman Personalization, sehingga setiap varian tema benar-benar
     * mengubah tampilan (bukan hanya --color-primary).
     *
     * Warna dasar yang dipakai:
     *   primary  -> warna utama navigasi, tombol, dan sidebar
     *   secondary-> slot accent di desain baru (CTA oranye seperti "Tambah Keranjang")
     *
     * @return array<string, string>
     */
    public static function paletteTokens(?Setting $setting = null): array
    {
        $setting ??= self::get();

        $primary = $setting->color_primary ?: '#4F46E5';
        $primaryDark = $setting->color_primary_dark ?: '#4338CA';

        // `color_secondary` kini berperan sebagai warna *accent* (CTA oranye).
        // installations lama masih menyimpan abu-abu netral di kolom ini karena
        // dulu tidak ada aturan CSS yang memakainya, jadi nilainya bisa saja
        // bukan warna accent yang diharapkan — pilih ulang varian di halaman
        // Personalization untuk memperbarui.
        $accent = $setting->color_secondary ?: '#F97316';
        $accentDark = $setting->color_secondary_dark ?: '#EA580C';

        $black = '#000000';
        $white = '#FFFFFF';
        $ink = '#020617';

        $tokens = [];

        foreach ([
            'primary' => $primary,
            'accent' => $accent,
            'success' => $setting->color_success ?: '#10B981',
            'warning' => $setting->color_warning ?: '#F59E0B',
            'danger' => $setting->color_danger ?: '#EF4444',
        ] as $name => $base) {
            $tokens["--color-{$name}"] = $base;
            $tokens["--color-{$name}-rgb"] = self::hexToRgb($base);
        }

        // Turunan terang: hover & soft. `mix()` memakai porsi warna asal, jadi
        // nilai kecil mendekati warna tujuan (putih untuk soft).
        $tokens['--color-primary-hover'] = $primaryDark;
        $tokens['--color-primary-active'] = self::mix($primaryDark, $black, 0.82);
        $tokens['--color-primary-soft'] = self::mix($primary, $white, 0.12);
        $tokens['--color-accent-hover'] = $accentDark;
        $tokens['--color-accent-active'] = self::mix($accentDark, $black, 0.82);
        $tokens['--color-accent-soft'] = self::mix($accent, $white, 0.14);

        foreach (['success', 'warning', 'danger'] as $name) {
            $base = $tokens["--color-{$name}"];
            $tokens["--color-{$name}-hover"] = self::mix($base, $black, 0.88);
            $tokens["--color-{$name}-soft"] = self::mix($base, $white, 0.12);
        }

        $tokens['--color-primary-dark'] = $primaryDark;

        // Varian gelap: warna brand dinaikkan sedikit agar tetap terbaca di
        // atas latar gelap, sementara surface/soft tetap gelap. `mix()` memakai
        // porsi warna asal, jadi makin besar nilainya makin dekat ke aslinya.
        $darkPrimary = self::mix($primary, $white, 0.85);
        $darkPrimaryDark = self::mix($primaryDark, $white, 0.88);
        $darkAccent = self::mix($accent, $white, 0.85);
        $darkAccentDark = self::mix($accentDark, $white, 0.88);

        $dark = [
            '--color-primary' => $darkPrimary,
            '--color-primary-rgb' => self::hexToRgb($darkPrimary),
            '--color-primary-dark' => $darkPrimaryDark,
            '--color-primary-hover' => self::mix($darkPrimary, $white, 0.92),
            '--color-primary-active' => $darkPrimary,
            '--color-primary-soft' => self::mix($darkPrimary, $ink, 0.22),
            '--color-accent' => $darkAccent,
            '--color-accent-rgb' => self::hexToRgb($darkAccent),
            '--color-accent-dark' => $darkAccentDark,
            '--color-accent-hover' => self::mix($darkAccent, $white, 0.92),
            '--color-accent-active' => $darkAccent,
            '--color-accent-soft' => self::mix($darkAccent, $ink, 0.24),
        ];

        foreach (['success', 'warning', 'danger'] as $name) {
            $lifted = self::mix($tokens["--color-{$name}"], $white, 0.85);
            $dark["--color-{$name}"] = $lifted;
            $dark["--color-{$name}-rgb"] = self::hexToRgb($lifted);
            $dark["--color-{$name}-hover"] = self::mix($lifted, $white, 0.92);
            $dark["--color-{$name}-soft"] = self::mix($lifted, $ink, 0.25);
        }

        return ['light' => $tokens, 'dark' => $dark];
    }

    /**
     * Token CSS sidebar yang diturunkan dari warna tema aktif.
     *
     * Sidebar sengaja tidak memakai warna hardcoded supaya ikut berubah
     * bersama tema yang dipilih di halaman Personalization.
     *
     * @return array<string, string>
     */
    public static function sidebarTokens(?Setting $setting = null): array
    {
        $setting ??= self::get();

        $primary = $setting->color_primary ?: '#4F46E5';
        $primaryDark = $setting->color_primary_dark ?: '#4338CA';
        $primaryRgb = self::hexToRgb($primary);

        return [
            '--color-sidebar-bg' => $primaryDark,
            '--color-sidebar-bg-gradient' => "linear-gradient(180deg, {$primary} 0%, {$primaryDark} 100%)",
            '--color-sidebar-text' => '#E0E7FF',
            '--color-sidebar-text-muted' => 'rgba(224, 231, 255, 0.6)',
            '--color-sidebar-border' => 'rgba(255, 255, 255, 0.08)',
            '--color-sidebar-hover' => 'rgba(255, 255, 255, 0.1)',
            '--color-sidebar-active-bg' => "linear-gradient(135deg, rgba({$primaryRgb}, 0.25), rgba({$primaryRgb}, 0.1))",
            '--color-sidebar-active-border' => "rgba({$primaryRgb}, 0.35)",
            '--color-sidebar-brand-gradient' => "linear-gradient(135deg, {$primary} 0%, {$primaryDark} 100%)",
            '--color-footer-bg' => $primaryDark,
            '--color-footer-text' => '#FFFFFF',
        ];
    }

    /**
     * Render token brand + sidebar sebagai blok CSS siap tempel di <style>.
     *
     * Mengembalikan blok `:root { ... }` dan `[data-theme="dark"] { ... }`
     * secara terpisah. Kedua selektor punya spesifisitas sama (0,1,0), jadi
     * blok gelap harus berada setelah blok terang agar menang.
     */
    public static function sidebarCss(?Setting $setting = null): string
    {
        $palette = self::paletteTokens($setting);

        $lines = [];
        $lines[] = ':root {';
        $lines[] = self::renderDeclarations($palette['light']);
        $lines[] = '    /* Sidebar & footer mengikuti primary (lihat SettingsHelper::sidebarTokens) */';
        $lines[] = self::renderDeclarations(self::sidebarTokens($setting));
        $lines[] = '}';
        $lines[] = '';
        $lines[] = '[data-theme="dark"] {';
        $lines[] = self::renderDeclarations($palette['dark']);
        $lines[] = '}';

        return implode("\n", $lines);
    }

    /**
     * Render token warna netral (latar, border, teks) untuk sebuah varian tema.
     */
    public static function surfaceCss(?string $variant = null): string
    {
        $variant ??= self::get()->theme_variant;

        $surfaces = ThemePresets::surfaces($variant ?: 'default');

        $declarations = array_map(
            // Kunci preset memakai underscore (color_bg) agar mudah dibaca di
            // PHP, sedangkan token CSS memakai tanda hubung (--color-bg).
            fn (string $name, string $value) => '    --'.str_replace('_', '-', $name).": {$value};",
            array_keys($surfaces),
            array_values($surfaces),
        );

        return implode("\n", $declarations);
    }

    /**
     * @param  array<string, string>  $tokens
     */
    private static function renderDeclarations(array $tokens, string $indent = '    '): string
    {
        $declarations = array_map(
            fn (string $name, string $value) => "{$indent}{$name}: {$value};",
            array_keys($tokens),
            array_values($tokens),
        );

        return implode("\n", $declarations);
    }
}
