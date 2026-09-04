<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dashboard_widgets')) {
            Schema::create('dashboard_widgets', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('key');
                $table->string('title');
                $table->string('type');
                $table->json('configuration')->nullable();
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();
                $table->unique(['tenant_id', 'key']);
                $table->index(['tenant_id', 'position']);
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('event');
                $table->nullableMorphs('auditable');
                $table->ipAddress('ip_address')->nullable();
                $table->string('user_agent', 1000)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['tenant_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('tenant_notifications')) {
            Schema::create('tenant_notifications', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type');
                $table->string('title');
                $table->text('body');
                $table->json('data')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'user_id', 'read_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_notifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('dashboard_widgets');
    }
};
