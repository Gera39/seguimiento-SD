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
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 200)->unique();
            $table->string('short_name', 80)->nullable();
            $table->string('educational_level', 50);
            $table->unsignedTinyInteger('duration_terms');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 200)->unique();
            $table->string('subject_type', 30)->default('REGULAR');
            $table->decimal('default_total_hours', 6, 2)->nullable();
            $table->decimal('default_theoretical_hours', 6, 2)->nullable();
            $table->decimal('default_practical_hours', 6, 2)->nullable();
            $table->decimal('credits', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status_code', 20)->default('PLANNED');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_period_id')->constrained()->cascadeOnDelete();
            $table->string('group_code', 20);
            $table->string('shift_code', 20);
            $table->unsignedTinyInteger('term_number');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['career_id', 'academic_period_id', 'group_code'], 'groups_scope_unique');
        });

        Schema::create('career_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('term_number');
            $table->string('curricular_block', 100)->nullable();
            $table->decimal('total_hours', 6, 2);
            $table->decimal('theoretical_hours', 6, 2)->default(0);
            $table->decimal('practical_hours', 6, 2)->default(0);
            $table->decimal('credits', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['career_id', 'subject_id', 'term_number'], 'career_subjects_curriculum_unique');
        });

        Schema::create('group_subject_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('career_subject_id')->constrained()->cascadeOnDelete();
            $table->string('modality_code', 20)->default('PRESENTIAL');
            $table->date('scheduled_start_date')->nullable();
            $table->date('scheduled_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['group_id', 'career_subject_id'], 'group_subject_offerings_scope_unique');
        });

        Schema::create('teacher_subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_subject_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assignment_role_code', 20)->default('PRIMARY');
            $table->string('assignment_status_code', 20)->default('ACTIVE');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('released_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['group_subject_offering_id', 'teacher_user_id'], 'teacher_subject_assignments_scope_unique');
            $table->index(['teacher_user_id', 'is_active'], 'teacher_subject_assignments_teacher_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_subject_assignments');
        Schema::dropIfExists('group_subject_offerings');
        Schema::dropIfExists('career_subjects');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('academic_periods');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('careers');
    }
};
