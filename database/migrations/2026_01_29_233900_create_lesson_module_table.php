<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create pivot table for many-to-many relationship
        Schema::create('lesson_module', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Each lesson can only appear once per module
            $table->unique(['lesson_id', 'module_id']);
        });

        // Make module_id nullable in lessons table (lesson exists independently)
        // Note: We'll migrate existing data from module_id to pivot table
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_module');
    }
};
