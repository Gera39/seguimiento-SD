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
            $table->string('employee_number', 30)->nullable()->unique()->after('id');
            $table->boolean('must_change_password')->default(false)->after('remember_token');
            $table->boolean('is_active')->default(true)->after('must_change_password');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->boolean('is_mfa_verified')->default(false)->after('last_activity');
            $table->timestamp('last_mfa_passed_at')->nullable()->after('is_mfa_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn([
                'is_mfa_verified',
                'last_mfa_passed_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_employee_number_unique');
            $table->dropColumn([
                'employee_number',
                'must_change_password',
                'is_active',
                'last_login_at',
            ]);
        });
    }
};
