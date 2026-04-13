<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('didactic_plan_review_comments', function (Blueprint $table) {
            $table->string('field_path', 150)->nullable()->after('entity_id');
            $table->string('field_label', 200)->nullable()->after('field_path');
            $table->text('observed_value_snapshot')->nullable()->after('comment_text');
            $table->string('comment_status_code', 20)->default('OPEN')->after('observed_value_snapshot');
            $table->text('teacher_response')->nullable()->after('comment_status_code');
            $table->foreignId('teacher_responded_by_user_id')->nullable()->after('teacher_response')->constrained('users')->nullOnDelete();
            $table->timestamp('teacher_responded_at')->nullable()->after('teacher_responded_by_user_id');
            $table->text('updated_value_snapshot')->nullable()->after('teacher_responded_at');
            $table->foreignId('validated_by_user_id')->nullable()->after('updated_value_snapshot')->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable()->after('validated_by_user_id');

            $table->index(['comment_status_code'], 'didactic_plan_review_comments_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('didactic_plan_review_comments', function (Blueprint $table) {
            $table->dropIndex('didactic_plan_review_comments_status_index');
            $table->dropConstrainedForeignId('validated_by_user_id');
            $table->dropColumn('validated_at');
            $table->dropColumn('updated_value_snapshot');
            $table->dropColumn('teacher_responded_at');
            $table->dropConstrainedForeignId('teacher_responded_by_user_id');
            $table->dropColumn('teacher_response');
            $table->dropColumn('comment_status_code');
            $table->dropColumn('observed_value_snapshot');
            $table->dropColumn('field_label');
            $table->dropColumn('field_path');
        });
    }
};
