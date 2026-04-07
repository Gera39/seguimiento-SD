<?php

namespace App\Domain\Planning\Services;

use App\Domain\Planning\Enums\PlanningStatusCode;
use App\Domain\Planning\Enums\PlanningValidationContext;
use App\Models\DidacticPlan;
use App\Models\EvaluationCriterionType;
use App\Models\PlanningStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DidacticPlanUpsertService
{
    public function __construct(
        protected DidacticPlanValidationService $validationService,
    ) {
    }

    public function create(User $user, array $payload): DidacticPlan
    {
        return DB::transaction(function () use ($user, $payload) {
            $statusId = PlanningStatus::query()
                ->where('code', PlanningStatusCode::DRAFT->value)
                ->value('id');

            $plan = DidacticPlan::query()->create([
                'plan_folio' => $payload['plan_folio'] ?? $this->generatePlanFolio(),
                'teacher_subject_assignment_id' => $payload['teacher_subject_assignment_id'],
                'status_id' => $statusId,
                'general_objective' => $payload['general_objective'],
                'course_intent' => $payload['course_intent'] ?? null,
                'methodology_notes' => $payload['methodology_notes'] ?? null,
                'general_observations' => $payload['general_observations'] ?? null,
                'created_by_user_id' => $user->id,
                'updated_by_user_id' => $user->id,
            ]);

            $this->syncNestedData($plan, $payload);
            $this->validationService->validateAndCapture($plan->fresh(['units.modules', 'evaluationCriteria']), PlanningValidationContext::DRAFT_SAVE, $user);

            return $plan->fresh(['status', 'assignment.offering.careerSubject.subject', 'assignment.offering.group']);
        });
    }

    public function update(User $user, DidacticPlan $plan, array $payload): DidacticPlan
    {
        return DB::transaction(function () use ($user, $plan, $payload) {
            $plan->fill([
                'teacher_subject_assignment_id' => $payload['teacher_subject_assignment_id'],
                'general_objective' => $payload['general_objective'],
                'course_intent' => $payload['course_intent'] ?? null,
                'methodology_notes' => $payload['methodology_notes'] ?? null,
                'general_observations' => $payload['general_observations'] ?? null,
                'updated_by_user_id' => $user->id,
            ]);

            $plan->save();

            $this->syncNestedData($plan, $payload);
            $this->validationService->validateAndCapture($plan->fresh(['units.modules', 'evaluationCriteria']), PlanningValidationContext::DRAFT_SAVE, $user);

            return $plan->fresh(['status', 'assignment.offering.careerSubject.subject', 'assignment.offering.group']);
        });
    }

    protected function syncNestedData(DidacticPlan $plan, array $payload): void
    {
        $plan->references()->delete();
        $plan->evaluationCriteria()->delete();
        $plan->units()->delete();

        $unitMap = [];

        foreach ($payload['units'] ?? [] as $unitData) {
            $unit = $plan->units()->create([
                'unit_number' => $unitData['unit_number'],
                'title' => $unitData['title'],
                'learning_objective' => $unitData['learning_objective'],
                'planned_hours' => $unitData['planned_hours'],
                'progress_percentage' => $unitData['progress_percentage'],
                'start_week' => $unitData['start_week'] ?? null,
                'end_week' => $unitData['end_week'] ?? null,
                'teaching_strategy' => $unitData['teaching_strategy'] ?? null,
                'learning_evidence' => $unitData['learning_evidence'] ?? null,
                'assessment_strategy' => $unitData['assessment_strategy'] ?? null,
            ]);

            $unitMap[$unit->unit_number] = $unit->id;

            foreach ($unitData['modules'] ?? [] as $moduleData) {
                $unit->modules()->create([
                    'module_number' => $moduleData['module_number'],
                    'title' => $moduleData['title'],
                    'topic_description' => $moduleData['topic_description'],
                    'theoretical_hours' => $moduleData['theoretical_hours'],
                    'practical_hours' => $moduleData['practical_hours'],
                    'learning_activity' => $moduleData['learning_activity'] ?? null,
                    'teaching_activity' => $moduleData['teaching_activity'] ?? null,
                    'resources' => $moduleData['resources'] ?? null,
                    'assessment_activity' => $moduleData['assessment_activity'] ?? null,
                    'delivery_mode' => $moduleData['delivery_mode'] ?? 'PRESENTIAL',
                    'scheduled_date' => $moduleData['scheduled_date'] ?? null,
                ]);
            }
        }

        $criterionTypes = EvaluationCriterionType::query()
            ->pluck('id', 'code');

        foreach ($payload['evaluation_criteria'] ?? [] as $criterionData) {
            $plan->evaluationCriteria()->create([
                'didactic_plan_unit_id' => isset($criterionData['unit_number']) ? ($unitMap[$criterionData['unit_number']] ?? null) : null,
                'criterion_type_id' => $criterionTypes[$criterionData['criterion_type_code']],
                'criterion_name' => $criterionData['criterion_name'],
                'description' => $criterionData['description'],
                'evidence_description' => $criterionData['evidence_description'],
                'instrument_description' => $criterionData['instrument_description'],
                'weight_percentage' => $criterionData['weight_percentage'],
                'minimum_score' => $criterionData['minimum_score'] ?? null,
                'sort_order' => $criterionData['sort_order'] ?? 1,
            ]);
        }

        foreach ($payload['references'] ?? [] as $referenceData) {
            $plan->references()->create([
                'reference_type' => $referenceData['reference_type'],
                'citation' => $referenceData['citation'],
                'sort_order' => $referenceData['sort_order'] ?? 1,
            ]);
        }
    }

    protected function generatePlanFolio(): string
    {
        return 'PLAN-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }
}
