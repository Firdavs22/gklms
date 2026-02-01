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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->json('answers'); // Store all answers as JSON
            $table->integer('score')->nullable(); // Score for quiz type
            $table->integer('max_score')->nullable(); // Maximum possible score
            $table->boolean('is_passed')->default(false);
            $table->timestamp('submitted_at');
            $table->timestamps();

            // User can only submit once per assignment
            $table->unique(['user_id', 'assignment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
