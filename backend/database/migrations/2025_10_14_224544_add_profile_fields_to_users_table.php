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
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('active');
            $table->boolean('force_password_reset')->default(true);
            $table->string('profile_photo_path')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamp('first_login_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->text('last_login_user_agent')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('locale')->default('en');
            $table->timestamp('blocked_at')->nullable();
            $table->text('profile_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'force_password_reset',
                'profile_photo_path',
                'password_changed_at',
                'first_login_at',
                'last_login_at',
                'last_login_ip',
                'last_login_user_agent',
                'timezone',
                'locale',
                'blocked_at',
                'profile_notes',
            ]);
        });
    }
};
