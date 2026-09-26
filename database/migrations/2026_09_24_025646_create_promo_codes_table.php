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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type', 10)->default('percent');
            $table->unsignedBigInteger('value')->default(0);
            $table->unsignedBigInteger('min_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamps();
        });

        // Add foreign keys to orders and transactions tables
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('promo_code_id')->references('id')->on('promo_codes')->nullOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('promo_code_id')->references('id')->on('promo_codes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['promo_code_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['promo_code_id']);
        });

        Schema::dropIfExists('promo_codes');
    }
};
