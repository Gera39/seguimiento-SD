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
        Schema::create('planning_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100)->unique();
            $table->boolean('is_teacher_editable');
            $table->boolean('is_teacher_deletable');
            $table->boolean('is_terminal')->default(false);
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();
        });

        Schema::create('evaluation_criterion_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('planning_transition_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_status_id')->constrained('planning_statuses')->cascadeOnDelete();
            $table->foreignId('to_status_id')->constrained('planning_statuses')->cascadeOnDelete();
            $table->foreignId('triggered_by_role_id')->constrained('roles')->cascadeOnDelete();
            $table->string('transition_code', 60)->unique();
            $table->boolean('requires_comment')->default(false);
            $table->boolean('reopens_for_editing')->default(false);
            $table->timestamps();
            $table->unique(['from_status_id', 'to_status_id', 'triggered_by_role_id'], 'planning_transition_rules_scope_unique');
        });

        Schema::create('didactic_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_uuid')->unique();
            $table->string('plan_folio', 30)->unique();
            $table->foreignId('teacher_subject_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('planning_statuses')->restrictOnDelete();
            $table->unsignedInteger('version_number')->default(1);
            $table->unsignedSmallInteger('current_review_round')->default(0);
            $table->text('general_objective');
            $table->text('course_intent')->nullable();
            $table->text('methodology_notes')->nullable();
            $table->text('general_observations')->nullable();
            $table->string('submission_notes', 500)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('feedback_released_at')->nullable();
            $table->timestamp('authorized_at')->nullable();
            $table->foreignId('authorized_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['teacher_subject_assignment_id', 'status_id'], 'didactic_plans_assignment_status_index');
            $table->index(['status_id', 'created_at'], 'didactic_plans_status_created_index');
        });

        Schema::create('didactic_plan_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('didactic_plan_id')->constrained()->cascadeOnDelete();
            $table->string('reference_type', 20);
            $table->string('citation', 1000);
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
            $table->index(['didactic_plan_id', 'sort_order'], 'didactic_plan_references_plan_sort_index');
        });

        Schema::create('didactic_plan_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('didactic_plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('unit_number');
            $table->string('title', 200);
            $table->text('learning_objective');
            $table->decimal('planned_hours', 6, 2);
            $table->decimal('progress_percentage', 5, 2);
            $table->unsignedTinyInteger('start_week')->nullable();
            $table->unsignedTinyInteger('end_week')->nullable();
            $table->text('teaching_strategy')->nullable();
            $table->text('learning_evidence')->nullable();
            $table->text('assessment_strategy')->nullable();
            $table->timestamps();
            $table->unique(['didactic_plan_id', 'unit_number'], 'didactic_plan_units_scope_unique');
            $table->index(['didactic_plan_id', 'unit_number'], 'didactic_plan_units_plan_number_index');
        });

        Schema::create('didactic_plan_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('didactic_plan_unit_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('module_number');
            $table->string('title', 200);
            $table->text('topic_description');
            $table->decimal('theoretical_hours', 6, 2)->default(0);
            $table->decimal('practical_hours', 6, 2)->default(0);
            $table->text('learning_activity')->nullable();
            $table->text('teaching_activity')->nullable();
            $table->text('resources')->nullable();
            $table->text('assessment_activity')->nullable();
            $table->string('delivery_mode', 20)->default('PRESENTIAL');
            $table->date('scheduled_date')->nullable();
            $table->timestamps();
            $table->unique(['didactic_plan_unit_id', 'module_number'], 'didactic_plan_modules_scope_unique');
            $table->index(['didactic_plan_unit_id', 'module_number'], 'didactic_plan_modules_unit_number_index');
        });

        Schema::create('didactic_plan_evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('didactic_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('didactic_plan_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('criterion_type_id')->constrained('evaluation_criterion_types')->restrictOnDelete();
            $table->string('criterion_name', 150);
            $table->string('description', 500);
            $table->string('evidence_description', 500);
            $table->string('instrument_description', 250);
            $table->decimal('weight_percentage', 5, 2);
            $table->decimal('minimum_score', 5, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
            $table->index(['didactic_plan_id', 'sort_order'], 'didactic_plan_evaluation_plan_sort_index');
            $table->index(['didactic_plan_unit_id'], 'didactic_plan_evaluation_unit_index');
        });

        Schema::create('didactic_plan_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('didactic_plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('review_round');
            $table->string('review_stage_code', 20);
            $table->foreignId('reviewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decision_status_id')->nullable()->constrained('planning_statuses')->nullOnDelete();
            $table->string('general_comments', 1500)->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['didactic_plan_id', 'review_round', 'review_stage_code'], 'didactic_plan_reviews_scope_unique');
            $table->index(['reviewer_user_id', 'started_at'], 'didactic_plan_reviews_reviewer_started_index');
        });

        Schema::create('didactic_plan_review_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('didactic_plan_reviews')->cascadeOnDelete();
            $table->string('entity_type', 20);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('severity_code', 20);
            $table->string('comment_text', 1000);
            $table->boolean('is_resolved')->default(false);
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['review_id', 'severity_code', 'is_resolved'], 'didactic_plan_review_comments_scope_index');
        });

        Schema::create('didactic_plan_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('didactic_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_status_id')->nullable()->constrained('planning_statuses')->nullOnDelete();
            $table->foreignId('to_status_id')->constrained('planning_statuses')->cascadeOnDelete();
            $table->foreignId('transition_rule_id')->nullable()->constrained('planning_transition_rules')->nullOnDelete();
            $table->foreignId('changed_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('comments', 500)->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->index(['didactic_plan_id', 'changed_at'], 'didactic_plan_status_history_plan_changed_index');
        });

        Schema::create('didactic_plan_validation_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('didactic_plan_id')->constrained()->cascadeOnDelete();
            $table->string('validation_context', 30);
            $table->unsignedSmallInteger('total_units');
            $table->unsignedInteger('total_modules');
            $table->decimal('total_unit_hours', 8, 2);
            $table->decimal('total_module_hours', 8, 2);
            $table->decimal('total_progress_percentage', 6, 2);
            $table->decimal('total_evaluation_percentage', 6, 2);
            $table->boolean('is_valid');
            $table->longText('validation_details')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['didactic_plan_id', 'created_at'], 'didactic_plan_validation_snapshots_plan_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('didactic_plan_validation_snapshots');
        Schema::dropIfExists('didactic_plan_status_history');
        Schema::dropIfExists('didactic_plan_review_comments');
        Schema::dropIfExists('didactic_plan_reviews');
        Schema::dropIfExists('didactic_plan_evaluation_criteria');
        Schema::dropIfExists('didactic_plan_modules');
        Schema::dropIfExists('didactic_plan_units');
        Schema::dropIfExists('didactic_plan_references');
        Schema::dropIfExists('didactic_plans');
        Schema::dropIfExists('planning_transition_rules');
        Schema::dropIfExists('evaluation_criterion_types');
        Schema::dropIfExists('planning_statuses');
    }
};
