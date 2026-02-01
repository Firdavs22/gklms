<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Assignments - attached to lessons
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['text', 'poll', 'quiz']); // text=open answer, poll=survey, quiz=test
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('show_correct_answers')->default(true);
            $table->boolean('is_required')->default(false); // Required to proceed?
            $table->timestamps();
        });

        // Questions
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->enum('type', ['text', 'single', 'multiple']); // text=free text, single=one answer, multiple=many
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Answer options for single/multiple choice
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->boolean('is_correct')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // User responses
        Schema::create('user_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('answer_id')->nullable()->constrained()->nullOnDelete();
            $table->text('text_response')->nullable(); // For text questions
            $table->boolean('is_correct')->nullable(); // null for text/poll, true/false for quiz
            $table->timestamps();
            
            // Each user can only respond once per question (for text) or once per answer (for choice)
            $table->index(['user_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_responses');
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('assignments');
    }
};
