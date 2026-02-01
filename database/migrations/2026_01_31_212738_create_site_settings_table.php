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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('site_settings')->insert([
            ['key' => 'site_name', 'value' => 'GloboKids Edu', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'logo_path', 'value' => null, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'primary_color', 'value' => '#7c3aed', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'secondary_color', 'value' => '#a855f7', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
