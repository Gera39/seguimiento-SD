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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(true);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('name', 120)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('user_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('career_id')->nullable()->constrained()->nullOnDelete();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'is_active'], 'user_role_assignments_user_active_index');
            $table->index(['role_id', 'career_id', 'is_active'], 'user_role_assignments_scope_index');
        });

        Schema::create('user_mfa_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('method_type', 20);
            $table->string('label', 100);
            $table->string('secret_encrypted', 500)->nullable();
            $table->string('destination_masked', 120)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'is_primary'], 'user_mfa_methods_user_primary_index');
        });

        Schema::create('user_mfa_recovery_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mfa_method_id')->constrained('user_mfa_methods')->cascadeOnDelete();
            $table->string('code_hash');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('auth_login_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable();
            $table->foreignId('mfa_method_id')->nullable()->constrained('user_mfa_methods')->nullOnDelete();
            $table->string('event_type', 30);
            $table->boolean('is_success');
            $table->string('failure_reason', 250)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->index(['user_id', 'occurred_at'], 'auth_login_audits_user_occurred_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_login_audits');
        Schema::dropIfExists('user_mfa_recovery_codes');
        Schema::dropIfExists('user_mfa_methods');
        Schema::dropIfExists('user_role_assignments');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
