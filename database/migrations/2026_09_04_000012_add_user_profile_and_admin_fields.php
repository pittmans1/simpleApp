<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'theme')) {
                $table->string('theme')->default('light');
            }
            if (! Schema::hasColumn('users', 'achievements')) {
                $table->json('achievements')->nullable();
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->index();
            }
            if (! Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->index();
            }
            if (! Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url')->nullable();
            }
            if (! Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->nullable();
            }
            if (! Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 10)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            foreach (['theme', 'achievements', 'role', 'is_super_admin', 'avatar_url', 'timezone', 'locale'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
