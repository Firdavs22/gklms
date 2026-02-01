<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            // Add module_id column
            $table->foreignId('module_id')->nullable()->after('id');
            
            // Add video source type column
            $table->enum('video_source', ['url', 'yandex_disk', 'upload'])->default('url')->after('video_url');
            $table->string('video_path')->nullable()->after('video_source');
        });

        // Update foreign key after data migration would be done
        Schema::table('lessons', function (Blueprint $table) {
            $table->foreign('module_id')->references('id')->on('modules')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropColumn(['module_id', 'video_source', 'video_path']);
        });
    }
};
