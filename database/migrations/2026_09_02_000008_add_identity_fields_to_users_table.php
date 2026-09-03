<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('user')->after('theme');
            $table->boolean('is_super_admin')->default(false)->after('role');
            $table->string('avatar_url')->nullable()->after('is_super_admin');
            $table->string('oauth_provider')->nullable()->after('avatar_url');
            $table->string('oauth_id')->nullable()->after('oauth_provider');
            $table->text('oauth_token')->nullable()->after('oauth_id');
            $table->text('oauth_refresh_token')->nullable()->after('oauth_token');
            $table->timestamp('oauth_token_expires_at')->nullable()->after('oauth_refresh_token');
            $table->string('timezone')->nullable()->after('oauth_token_expires_at');
            $table->string('locale', 10)->nullable()->after('timezone');
            $table->unique(['oauth_provider', 'oauth_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['oauth_provider', 'oauth_id']);
            $table->dropColumn([
                'role', 'is_super_admin', 'avatar_url', 'oauth_provider', 'oauth_id',
                'oauth_token', 'oauth_refresh_token', 'oauth_token_expires_at', 'timezone', 'locale',
            ]);
        });
    }
};
