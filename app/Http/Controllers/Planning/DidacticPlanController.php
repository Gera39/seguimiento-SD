<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\StoreDidacticPlanRequest;
use App\Http\Requests\Planning\UpdateDidacticPlanRequest;
use App\Models\DidacticPlan;
use App\Models\EvaluationCriterionType;
use App\Models\TeacherSubjectAssignment;
use App\Domain\Planning\Services\DidacticPlanUpsertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DidacticPlanController extends Controller
{
    public function __construct(
        protected DidacticPlanUpsertService $upsertService,
    ) {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', DidacticPlan::class);

        $plans = DidacticPlan::query()
            ->with([
                'status',
                'assignment.offering.careerSubject.subject',
                'assignment.offering.group',
                'validationSnapshots',
            ])
            ->whereHas('assignment', fn ($query) => $query->where('teacher_user_id', $request->user()->id))
            ->latest('updated_at')
            ->get()
            ->map(fn (DidacticPlan $plan) => $this->serializePlanCard($plan))
            ->all();

        return Inertia::render('Secuencias', [
            'plans' => $plans,
            'createUrl' => route('plans.create', absolute: false),
            'status' => session('status'),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', DidacticPlan::class);

        return Inertia::render('Planning/Editor', [
            'mode' => 'create',
            'plan' => null,
            'assignments' => $this->assignmentOptions($request->user()->id),
            'criterionTypes' => $this->criterionTypes(),
            'storeUrl' => route('plans.store', absolute: false),
            'backUrl' => route('demo.secuencias', absolute: false),
        ]);
    }

    public function store(StoreDidacticPlanRequest $request): RedirectResponse
    {
        $this->authorize('create', DidacticPlan::class);

        $plan = $this->upsertService->create($request->user(), $request->validated());

        return redirect()
            ->route('plans.show', $plan)
            ->with('status', 'La planeacion se guardo como borrador.');
    }

    public function show(DidacticPlan $didacticPlan): Response
    {
        $didacticPlan->loadMissing([
            'status',
            'assignment.teacher',
            'assignment.offering.careerSubject.subject',
            'assignment.offering.group.academicPeriod',
            'assignment.offering.group.career',
            'units.modules',
            'references',
            'evaluationCriteria.criterionType',
            'validationSnapshots',
            'reviews.comments',
        ]);

        $this->authorize('view', $didacticPlan);

        return Inertia::render('VisualizacionSecuencia', [
            'plan' => $this->serializePlanDetail($didacticPlan),
            'status' => session('status'),
        ]);
    }

    public function edit(DidacticPlan $didacticPlan): Response
    {
        $didacticPlan->loadMissing([
            'assignment',
            'status',
            'units.modules',
            'references',
            'evaluationCriteria.criterionType',
        ]);

        $this->authorize('update', $didacticPlan);

        return Inertia::render('Planning/Editor', [
            'mode' => 'edit',
            'plan' => $this->serializePlanForEditor($didacticPlan),
            'assignments' => $this->assignmentOptions($didacticPlan->assignment->teacher_user_id),
            'criterionTypes' => $this->criterionTypes(),
            'updateUrl' => route('plans.update', $didacticPlan, absolute: false),
            'deleteUrl' => route('plans.destroy', $didacticPlan, absolute: false),
            'submitUrl' => route('plans.submit', $didacticPlan, absolute: false),
            'backUrl' => route('plans.show', $didacticPlan, absolute: false),
        ]);
    }

    public function update(UpdateDidacticPlanRequest $request, DidacticPlan $didacticPlan): RedirectResponse
    {
        $didacticPlan->loadMissing('status', 'assignment');
        $this->authorize('update', $didacticPlan);

        $plan = $this->upsertService->update($request->user(), $didacticPlan, $request->validated());

        return redirect()
            ->route('plans.show', $plan)
            ->with('status', 'La planeacion se actualizo correctamente.');
    }

    public function destroy(DidacticPlan $didacticPlan): RedirectResponse
    {
        $didacticPlan->loadMissing('status', 'assignment');
        $this->authorize('delete', $didacticPlan);

        $didacticPlan->delete();

        return redirect()
            ->route('demo.secuencias')
            ->with('status', 'La planeacion se elimino correctamente.');
    }

    protected function assignmentOptions(int $userId): array
    {
        return TeacherSubjectAssignment::query()
            ->with([
                'offering.careerSubject.subject',
                'offering.group',
            ])
            ->where('teacher_user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->map(function (TeacherSubjectAssignment $assignment): array {
                $subject = $assignment->offering?->careerSubject?->subject?->name ?? 'Asignatura';
                $group = $assignment->offering?->group?->group_code ?? 'Grupo';

                return [
                    'id' => $assignment->id,
                    'label' => "$subject - Grupo $group",
                ];
            })
            ->all();
    }

    protected function criterionTypes(): array
    {
        return EvaluationCriterionType::query()
            ->orderBy('name')
            ->get()
            ->map(fn (EvaluationCriterionType $type) => [
                'code' => $type->code,
                'name' => $type->name,
            ])
            ->all();
    }

    protected function serializePlanCard(DidacticPlan $plan): array
    {
        $subject = $plan->assignment?->offering?->careerSubject?->subject?->name ?? 'Sin asignatura';
        $group = $plan->assignment?->offering?->group?->group_code ?? 'Sin grupo';
        $latestSnapshot = $plan->validationSnapshots->sortByDesc('created_at')->first();

        return [
            'id' => $plan->id,
            'folio' => $plan->plan_folio,
            'title' => $subject,
            'subject' => $subject,
            'group' => $group,
            'updatedAt' => optional($plan->updated_at)->format('d M Y'),
            'status' => $plan->status?->name,
            'statusCode' => $plan->status?->code,
            'description' => str($plan->general_objective)->limit(120)->toString(),
            'validationSummary' => $latestSnapshot
                ? "{$latestSnapshot->total_progress_percentage}% avance / {$latestSnapshot->total_evaluation_percentage}% evaluacion"
                : 'Sin validaciones registradas',
            'href' => route('plans.show', $plan, absolute: false),
            'editUrl' => $plan->isTeacherEditable() ? route('plans.edit', $plan, absolute: false) : null,
        ];
    }

    protected function serializePlanDetail(DidacticPlan $plan): array
    {
        $latestSnapshot = $plan->validationSnapshots->sortByDesc('created_at')->first();

        return [
            'id' => $plan->id,
            'folio' => $plan->plan_folio,
            'status' => [
                'code' => $plan->status?->code,
                'name' => $plan->status?->name,
                'editable' => $plan->isTeacherEditable(),
            ],
            'teacher' => $plan->assignment?->teacher?->name,
            'subject' => $plan->assignment?->offering?->careerSubject?->subject?->name,
            'career' => $plan->assignment?->offering?->group?->career?->name,
            'group' => $plan->assignment?->offering?->group?->group_code,
            'period' => $plan->assignment?->offering?->group?->academicPeriod?->name,
            'general_objective' => $plan->general_objective,
            'course_intent' => $plan->course_intent,
            'methodology_notes' => $plan->methodology_notes,
            'general_observations' => $plan->general_observations,
            'submitted_at' => optional($plan->submitted_at)->format('d/m/Y H:i'),
            'authorized_at' => optional($plan->authorized_at)->format('d/m/Y H:i'),
            'summary' => [
                'units' => $plan->units->count(),
                'modules' => $plan->units->sum(fn ($unit) => $unit->modules->count()),
                'progress' => $latestSnapshot?->total_progress_percentage ?? round((float) $plan->units->sum('progress_percentage'), 2),
                'evaluation' => $latestSnapshot?->total_evaluation_percentage ?? round((float) $plan->evaluationCriteria->sum('weight_percentage'), 2),
            ],
            'units' => $plan->units->sortBy('unit_number')->values()->map(fn ($unit) => [
                'unit_number' => $unit->unit_number,
                'title' => $unit->title,
                'learning_objective' => $unit->learning_objective,
                'planned_hours' => $unit->planned_hours,
                'progress_percentage' => $unit->progress_percentage,
                'modules' => $unit->modules->sortBy('module_number')->values()->map(fn ($module) => [
                    'module_number' => $module->module_number,
                    'title' => $module->title,
                    'topic_description' => $module->topic_description,
                    'theoretical_hours' => $module->theoretical_hours,
                    'practical_hours' => $module->practical_hours,
                ])->all(),
            ])->all(),
            'evaluation_criteria' => $plan->evaluationCriteria->sortBy('sort_order')->values()->map(fn ($criterion) => [
                'criterion_name' => $criterion->criterion_name,
                'criterion_type' => $criterion->criterionType?->name,
                'weight_percentage' => $criterion->weight_percentage,
                'description' => $criterion->description,
                'evidence_description' => $criterion->evidence_description,
                'instrument_description' => $criterion->instrument_description,
            ])->all(),
            'references' => $plan->references->sortBy('sort_order')->values()->map(fn ($reference) => [
                'reference_type' => $reference->reference_type,
                'citation' => $reference->citation,
            ])->all(),
            'latest_validation' => $latestSnapshot ? [
                'context' => $latestSnapshot->validation_context,
                'created_at' => optional($latestSnapshot->created_at)->format('d/m/Y H:i'),
                'progress' => $latestSnapshot->total_progress_percentage,
                'evaluation' => $latestSnapshot->total_evaluation_percentage,
                'unit_hours' => $latestSnapshot->total_unit_hours,
                'module_hours' => $latestSnapshot->total_module_hours,
            ] : null,
            'actions' => [
                'edit' => auth()->user()?->can('update', $plan) ? route('plans.edit', $plan, absolute: false) : null,
                'submit' => auth()->user()?->can('submit', $plan) ? route('plans.submit', $plan, absolute: false) : null,
                'review' => auth()->user()?->hasRole(\App\Domain\Security\Enums\RoleCode::REVISOR)
                    ? route('plans.review.show', $plan, absolute: false)
                    : null,
                'final' => auth()->user()?->hasRole(\App\Domain\Security\Enums\RoleCode::DIRECTIVO)
                    ? route('plans.final.show', $plan, absolute: false)
                    : null,
            ],
        ];
    }

    protected function serializePlanForEditor(DidacticPlan $plan): array
    {
        return [
            'id' => $plan->id,
            'folio' => $plan->plan_folio,
            'teacher_subject_assignment_id' => $plan->teacher_subject_assignment_id,
            'general_objective' => $plan->general_objective,
            'course_intent' => $plan->course_intent,
            'methodology_notes' => $plan->methodology_notes,
            'general_observations' => $plan->general_observations,
            'status' => $plan->status?->name,
            'units' => $plan->units->sortBy('unit_number')->values()->map(fn ($unit) => [
                'unit_number' => $unit->unit_number,
                'title' => $unit->title,
                'learning_objective' => $unit->learning_objective,
                'planned_hours' => (float) $unit->planned_hours,
                'progress_percentage' => (float) $unit->progress_percentage,
                'start_week' => $unit->start_week,
                'end_week' => $unit->end_week,
                'teaching_strategy' => $unit->teaching_strategy,
                'learning_evidence' => $unit->learning_evidence,
                'assessment_strategy' => $unit->assessment_strategy,
                'modules' => $unit->modules->sortBy('module_number')->values()->map(fn ($module) => [
                    'module_number' => $module->module_number,
                    'title' => $module->title,
                    'topic_description' => $module->topic_description,
                    'theoretical_hours' => (float) $module->theoretical_hours,
                    'practical_hours' => (float) $module->practical_hours,
                    'learning_activity' => $module->learning_activity,
                    'teaching_activity' => $module->teaching_activity,
                    'resources' => $module->resources,
                    'assessment_activity' => $module->assessment_activity,
                    'delivery_mode' => $module->delivery_mode,
                    'scheduled_date' => optional($module->scheduled_date)->format('Y-m-d'),
                ])->all(),
            ])->all(),
            'evaluation_criteria' => $plan->evaluationCriteria->sortBy('sort_order')->values()->map(fn ($criterion) => [
                'unit_number' => optional($plan->units->firstWhere('id', $criterion->didactic_plan_unit_id))->unit_number,
                'criterion_type_code' => $criterion->criterionType?->code,
                'criterion_name' => $criterion->criterion_name,
                'description' => $criterion->description,
                'evidence_description' => $criterion->evidence_description,
                'instrument_description' => $criterion->instrument_description,
                'weight_percentage' => (float) $criterion->weight_percentage,
                'minimum_score' => $criterion->minimum_score !== null ? (float) $criterion->minimum_score : null,
                'sort_order' => $criterion->sort_order,
            ])->all(),
            'references' => $plan->references->sortBy('sort_order')->values()->map(fn ($reference) => [
                'reference_type' => $reference->reference_type,
                'citation' => $reference->citation,
                'sort_order' => $reference->sort_order,
            ])->all(),
        ];
    }
}
