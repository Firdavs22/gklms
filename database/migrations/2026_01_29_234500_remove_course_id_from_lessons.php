<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite we need to recreate the table without the course_id constraint
        // First, migrate any existing lessons to the pivot table if they have module_id
        
        // Check if lessons have module_id column
        if (Schema::hasColumn('lessons', 'module_id')) {
            // Migrate existing lesson-module relationships to pivot table
            $lessons = DB::table('lessons')->whereNotNull('module_id')->get();
            foreach ($lessons as $lesson) {
                DB::table('lesson_module')->insertOrIgnore([
                    'lesson_id' => $lesson->id,
                    'module_id' => $lesson->module_id,
                    'sort_order' => $lesson->sort_order ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // For SQLite, we need to recreate the table
        // Create a new lessons table without course_id and module_id constraints
        Schema::create('lessons_new', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('video_url')->nullable();
            $table->string('video_source')->default('url');
            $table->string('video_path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        // Copy data from old table
        DB::statement('INSERT INTO lessons_new (id, title, content, video_url, video_source, video_path, sort_order, is_published, created_at, updated_at) 
            SELECT id, title, content, video_url, COALESCE(video_source, "url"), video_path, sort_order, is_published, created_at, updated_at FROM lessons');

        // Drop old table and rename new one
        Schema::drop('lessons');
        Schema::rename('lessons_new', 'lessons');
    }

    public function down(): void
    {
        // Add back course_id and module_id columns
        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }
};
