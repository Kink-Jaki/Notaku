<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('social_instagram')->nullable();
            $table->string('social_facebook')->nullable();
            $table->string('social_tiktok')->nullable();
            $table->string('social_whatsapp')->nullable(); // format nomor: 62812xxxxxxx
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('contact_address')->nullable();
            $table->text('footer_tagline')->nullable(); // deskripsi singkat brand di footer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'social_instagram',
                'social_facebook',
                'social_tiktok',
                'social_whatsapp',
                'contact_email',
                'contact_phone',
                'contact_address',
                'footer_tagline',
            ]);
        });
    }
};
