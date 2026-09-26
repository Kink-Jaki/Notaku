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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name')->default('Notaku');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('color_primary')->default('#4F46E5');
            $table->string('color_primary_dark')->default('#4338CA');
            $table->string('color_success')->default('#10B981');
            $table->string('color_warning')->default('#F59E0B');
            $table->string('color_danger')->default('#EF4444');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
