<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Varian gelap (`midnight`) dihapus dari `ThemePresets` supaya pilihan
     * di halaman Personalization selalu berlatar terang. Mode gelap tetap
     * tersedia lewat toggle `data-theme`, bukan lewat preset varian.
     *
     * Baris settings yang masih menunjuk varian terhapus harus dikembalikan ke
     * `default` beserta warnanya — kalau hanya variant yang diubah, warna
     * navy yang tersisa akan terlihat sebagai preset "Default (Indigo)".
     *
     * Nilai ditulis literal (bukan lewat `ThemePresets`) supaya migration
     * tetap frozen meski palet aplikasi berubah di masa depan.
     */
    private const REMOVED_VARIANTS = ['midnight'];

    private const DEFAULT_PALETTE = [
        'color_primary' => '#4F46E5',
        'color_primary_dark' => '#4338CA',
        'color_secondary' => '#F97316',
        'color_secondary_dark' => '#EA580C',
        'color_success' => '#10B981',
        'color_warning' => '#F59E0B',
        'color_danger' => '#EF4444',
    ];

    public function up(): void
    {
        DB::table('settings')
            ->whereIn('theme_variant', self::REMOVED_VARIANTS)
            ->update(self::DEFAULT_PALETTE + ['theme_variant' => 'default']);
    }

    /**
     * Sengaja no-op.
     *
     * Data tidak bisa dikembalikan dengan aman: baris yang tadinya `default`
     * tidak bisa dibedakan dari yang tadinya `midnight`. Mengembalikan semua
     * baris `default` justru merusak baris yang tidak pernah berubah.
     */
    public function down(): void {}
};
