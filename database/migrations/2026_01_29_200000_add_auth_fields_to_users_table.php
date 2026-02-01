<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_id')->nullable()->unique()->after('email');
            $table->string('magic_token', 64)->nullable()->after('remember_token');
            $table->timestamp('magic_token_expires_at')->nullable()->after('magic_token');
            $table->boolean('is_admin')->default(false)->after('magic_token_expires_at');
            
            // Make password nullable for magic link / telegram users
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telegram_id', 'magic_token', 'magic_token_expires_at', 'is_admin']);
        });
    }
};
