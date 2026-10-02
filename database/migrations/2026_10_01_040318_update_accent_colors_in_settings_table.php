<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `color_secondary` kini dipakai sebagai slot *accent* desain baru.
     * Nilai default lama (#64748B abu) adalah sisa token "secondary" yang sudah
     * tidak terpakai, sehingga CTA toko tampak abu sampai varian dipilih ulang.
     * Samakan dengan accent preset `default` (#F97316 / #EA580C).
     */
    public function up(): void
    {
        DB::table('settings')
            ->where('color_secondary', '#64748B')
            ->update(['color_secondary' => '#F97316']);

        DB::table('settings')
            ->where('color_secondary_dark', '#475569')
            ->update(['color_secondary_dark' => '#EA580C']);

        Schema::table('settings', function (Blueprint $table) {
            $table->string('color_secondary')->default('#F97316')->change();
            $table->string('color_secondary_dark')->default('#EA580C')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('color_secondary')->default('#64748B')->change();
            $table->string('color_secondary_dark')->default('#475569')->change();
        });
    }
};
