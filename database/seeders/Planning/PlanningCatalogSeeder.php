<?php

namespace Database\Seeders\Planning;

use App\Domain\Planning\Enums\PlanningStatusCode;
use App\Domain\Security\Enums\RoleCode;
use App\Models\EvaluationCriterionType;
use App\Models\PlanningStatus;
use App\Models\PlanningTransitionRule;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PlanningCatalogSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $statuses = [
            PlanningStatusCode::DRAFT->value => ['name' => 'Borrador', 'is_teacher_editable' => true, 'is_teacher_deletable' => true, 'is_terminal' => false, 'sort_order' => 1],
            PlanningStatusCode::SUBMITTED->value => ['name' => 'Solicitada para revision', 'is_teacher_editable' => false, 'is_teacher_deletable' => false, 'is_terminal' => false, 'sort_order' => 2],
            PlanningStatusCode::UNDER_REVIEW->value => ['name' => 'En revision', 'is_teacher_editable' => false, 'is_teacher_deletable' => false, 'is_terminal' => false, 'sort_order' => 3],
            PlanningStatusCode::FEEDBACK->value => ['name' => 'Retroalimentacion emitida', 'is_teacher_editable' => true, 'is_teacher_deletable' => false, 'is_terminal' => false, 'sort_order' => 4],
            PlanningStatusCode::AUTHORIZED->value => ['name' => 'Autorizada', 'is_teacher_editable' => false, 'is_teacher_deletable' => false, 'is_terminal' => true, 'sort_order' => 5],
            PlanningStatusCode::REJECTED->value => ['name' => 'Rechazada', 'is_teacher_editable' => false, 'is_teacher_deletable' => false, 'is_terminal' => true, 'sort_order' => 6],
        ];

        foreach ($statuses as $code => $attributes) {
            PlanningStatus::query()->updateOrCreate(
                ['code' => $code],
                $attributes,
            );
        }

        $criterionTypes = [
            'DIAGNOSTIC' => ['name' => 'Diagnostico', 'description' => 'Evaluacion de diagnostico'],
            'FORMATIVE' => ['name' => 'Formativa', 'description' => 'Seguimiento durante el curso'],
            'SUMMATIVE' => ['name' => 'Sumativa', 'description' => 'Evaluacion final'],
        ];

        foreach ($criterionTypes as $code => $attributes) {
            EvaluationCriterionType::query()->updateOrCreate(
                ['code' => $code],
                $attributes,
            );
        }

        $transitions = [
            ['from' => PlanningStatusCode::DRAFT->value, 'to' => PlanningStatusCode::SUBMITTED->value, 'role' => RoleCode::DOCENTE->value, 'code' => 'DOCENTE_SUBMIT', 'requires_comment' => false, 'reopens_for_editing' => false],
            ['from' => PlanningStatusCode::FEEDBACK->value, 'to' => PlanningStatusCode::SUBMITTED->value, 'role' => RoleCode::DOCENTE->value, 'code' => 'DOCENTE_RESUBMIT', 'requires_comment' => false, 'reopens_for_editing' => false],
            ['from' => PlanningStatusCode::SUBMITTED->value, 'to' => PlanningStatusCode::UNDER_REVIEW->value, 'role' => RoleCode::REVISOR->value, 'code' => 'REVISOR_TAKE', 'requires_comment' => false, 'reopens_for_editing' => false],
            ['from' => PlanningStatusCode::UNDER_REVIEW->value, 'to' => PlanningStatusCode::FEEDBACK->value, 'role' => RoleCode::REVISOR->value, 'code' => 'REVISOR_FEEDBACK', 'requires_comment' => true, 'reopens_for_editing' => true],
            ['from' => PlanningStatusCode::UNDER_REVIEW->value, 'to' => PlanningStatusCode::AUTHORIZED->value, 'role' => RoleCode::DIRECTIVO->value, 'code' => 'DIRECTIVO_AUTHORIZE', 'requires_comment' => false, 'reopens_for_editing' => false],
            ['from' => PlanningStatusCode::UNDER_REVIEW->value, 'to' => PlanningStatusCode::REJECTED->value, 'role' => RoleCode::DIRECTIVO->value, 'code' => 'DIRECTIVO_REJECT', 'requires_comment' => true, 'reopens_for_editing' => false],
        ];

        foreach ($transitions as $transition) {
            PlanningTransitionRule::query()->updateOrCreate(
                ['transition_code' => $transition['code']],
                [
                    'from_status_id' => PlanningStatus::query()->where('code', $transition['from'])->value('id'),
                    'to_status_id' => PlanningStatus::query()->where('code', $transition['to'])->value('id'),
                    'triggered_by_role_id' => Role::query()->where('code', $transition['role'])->value('id'),
                    'requires_comment' => $transition['requires_comment'],
                    'reopens_for_editing' => $transition['reopens_for_editing'],
                ],
            );
        }
    }
}
