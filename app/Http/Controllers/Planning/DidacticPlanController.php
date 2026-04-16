<?php

namespace App\Http\Controllers\Planning;

use App\Domain\Planning\Services\DidacticPlanWordExportService;
use App\Domain\Planning\Enums\PlanningStatusCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\StoreDidacticPlanRequest;
use App\Http\Requests\Planning\UpdateDidacticPlanRequest;
use App\Models\DidacticPlan;
use App\Models\EvaluationCriterionType;
use App\Models\PlanningStatus;
use App\Models\TeacherSubjectAssignment;
use App\Domain\Planning\Services\DidacticPlanUpsertService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Inertia\Inertia;
use Inertia\Response;

class DidacticPlanController extends Controller
{
    public function __construct(
        protected DidacticPlanUpsertService $upsertService,
        protected DidacticPlanWordExportService $wordExportService,
    ) {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', DidacticPlan::class);

        $baseQuery = DidacticPlan::query()
            ->with([
                'status',
                'assignment.offering.careerSubject.subject',
                'assignment.offering.group.career',
                'assignment.offering.group.academicPeriod',
                'validationSnapshots',
                'reviews.comments',
                'statusHistory.toStatus',
            ])
            ->whereHas('assignment', fn ($query) => $query->where('teacher_user_id', $request->user()->id))
            ->whereHas('status');

        $allVisiblePlans = $this->sortTeacherPlans(
            $this->applyIndexFilters(clone $baseQuery, $request, false)->get(),
            $request->string('sort')->trim()->toString(),
        );

        $filteredPlans = $this->sortTeacherPlans(
            $this->applyIndexFilters(clone $baseQuery, $request, true)->get(),
            $request->string('sort')->trim()->toString(),
        )
            ->map(fn (DidacticPlan $plan) => $this->serializePlanCard($plan))
            ->all();

        return Inertia::render('Secuencias', [
            'baseUrl' => route('demo.secuencias', absolute: false),
            'filters' => [
                'search' => $request->string('search')->trim()->toString(),
                'status' => $this->sanitizeTeacherStatus($request->string('status')->trim()->toString()),
                'career' => $request->integer('career') ?: null,
                'period' => $request->integer('period') ?: null,
                'date_from' => $request->string('date_from')->trim()->toString(),
                'date_to' => $request->string('date_to')->trim()->toString(),
                'sort' => $this->sanitizeTeacherSort($request->string('sort')->trim()->toString()),
            ],
            'metrics' => $this->teacherMetrics($allVisiblePlans),
            'summary' => [
                'totalVisible' => $allVisiblePlans->count(),
                'filtered' => count($filteredPlans),
            ],
            'statusOptions' => $this->teacherStatusOptions(),
            'careerOptions' => $this->teacherCareerOptions($allVisiblePlans),
            'periodOptions' => $this->teacherPeriodOptions($allVisiblePlans),
            'sortOptions' => $this->teacherSortOptions(),
            'plans' => $filteredPlans,
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
        $this->loadPlanDetailRelations($didacticPlan);

        $this->authorize('view', $didacticPlan);

        return Inertia::render('VisualizacionSecuencia', [
            'plan' => $this->serializePlanDetail($didacticPlan),
            'status' => session('status'),
        ]);
    }

    public function exportWord(DidacticPlan $didacticPlan): BinaryFileResponse
    {
        $this->loadPlanDetailRelations($didacticPlan);
        $this->authorize('view', $didacticPlan);

        $filename = str($didacticPlan->plan_folio ?: 'planeacion')
            ->slug('_')
            ->append('.docx')
            ->toString();

        $summary = $this->serializePlanDetail($didacticPlan);
        $documentPath = $this->wordExportService->export($didacticPlan, $summary);

        return response()
            ->download(
                $documentPath,
                $filename,
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
            )
            ->deleteFileAfterSend(true);
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
        $career = $plan->assignment?->offering?->group?->career?->name ?? 'Sin carrera';
        $period = $plan->assignment?->offering?->group?->academicPeriod?->name ?? 'Sin periodo';
        $latestSnapshot = $plan->validationSnapshots->sortByDesc('created_at')->first();
        $openComments = $plan->reviews
            ->flatMap(fn ($review) => $review->comments)
            ->where('is_resolved', false)
            ->count();
        $latestTransition = $plan->statusHistory->sortByDesc('changed_at')->first();

        return [
            'id' => $plan->id,
            'folio' => $plan->plan_folio,
            'title' => $subject,
            'subject' => $subject,
            'career' => $career,
            'group' => $group,
            'period' => $period,
            'updatedAt' => optional($plan->updated_at)->format('d M Y'),
            'submittedAt' => optional($plan->submitted_at)->format('d/m/Y H:i'),
            'status' => $plan->status?->name,
            'statusCode' => $plan->status?->code,
            'description' => str($plan->general_objective)->limit(120)->toString(),
            'validationSummary' => $latestSnapshot
                ? "{$latestSnapshot->total_progress_percentage}% avance / {$latestSnapshot->total_evaluation_percentage}% evaluacion"
                : 'Sin validaciones registradas',
            'stateHint' => $this->teacherStateHint($plan),
            'openComments' => $openComments,
            'reviewRound' => $plan->current_review_round,
            'lastStatusChangeAt' => optional($latestTransition?->changed_at)->format('d/m/Y H:i'),
            'href' => route('plans.show', $plan, absolute: false),
            'editUrl' => $plan->isTeacherEditable() ? route('plans.edit', $plan, absolute: false) : null,
            'submitUrl' => auth()->user()?->can('submit', $plan) ? route('plans.submit', $plan, absolute: false) : null,
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
            'review_comments' => $plan->reviews
                ->flatMap(fn ($review) => $review->comments)
                ->sortByDesc('created_at')
                ->values()
                ->map(fn ($comment) => [
                    'id' => $comment->id,
                    'field_label' => $comment->field_label ?: $comment->entity_type,
                    'severity_code' => $comment->severity_code,
                    'comment_text' => $comment->comment_text,
                    'observed_value_snapshot' => $comment->observed_value_snapshot,
                    'teacher_response' => $comment->teacher_response,
                    'updated_value_snapshot' => $comment->updated_value_snapshot,
                    'comment_status_code' => $comment->comment_status_code,
                    'teacher_responded_at' => optional($comment->teacher_responded_at)->format('d/m/Y H:i'),
                    'validated_at' => optional($comment->validated_at)->format('d/m/Y H:i'),
                    'respond_url' => auth()->user()?->can('update', $plan)
                        ? route('plans.comments.respond', ['didacticPlan' => $plan, 'comment' => $comment], absolute: false)
                        : null,
                ])
                ->all(),
            'status_history' => $plan->statusHistory
                ->sortByDesc('changed_at')
                ->values()
                ->map(fn ($entry) => [
                    'from' => $entry->fromStatus?->name ?? 'Sin origen',
                    'to' => $entry->toStatus?->name ?? 'Sin destino',
                    'changed_at' => optional($entry->changed_at)->format('d/m/Y H:i'),
                    'changed_by' => $entry->changedBy?->name ?? 'Sistema',
                    'comments' => $entry->comments,
                ])
                ->all(),
            'actions' => [
                'edit' => auth()->user()?->can('update', $plan) ? route('plans.edit', $plan, absolute: false) : null,
                'submit' => auth()->user()?->can('submit', $plan) ? route('plans.submit', $plan, absolute: false) : null,
                'export_word' => auth()->user()?->can('view', $plan) ? route('plans.export-word', $plan, absolute: false) : null,
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

    protected function loadPlanDetailRelations(DidacticPlan $didacticPlan): void
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
            'statusHistory.fromStatus',
            'statusHistory.toStatus',
            'statusHistory.changedBy',
        ]);
    }

    protected function applyIndexFilters(Builder $query, Request $request, bool $includeFilters): Builder
    {
        if (! $includeFilters) {
            return $query;
        }

        $search = $request->string('search')->trim()->toString();
        $statusCode = $this->sanitizeTeacherStatus($request->string('status')->trim()->toString());
        $careerId = $request->integer('career');
        $periodId = $request->integer('period');
        $dateFrom = $request->string('date_from')->trim()->toString();
        $dateTo = $request->string('date_to')->trim()->toString();

        if ($search !== '') {
            $like = '%'.$search.'%';

            $query->where(function (Builder $builder) use ($like): void {
                $builder
                    ->where('plan_folio', 'like', $like)
                    ->orWhereHas('assignment.offering.careerSubject.subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', $like))
                    ->orWhereHas('assignment.offering.group', fn (Builder $groupQuery) => $groupQuery->where('group_code', 'like', $like))
                    ->orWhereHas('assignment.offering.group.career', fn (Builder $careerQuery) => $careerQuery->where('name', 'like', $like))
                    ->orWhereHas('assignment.offering.group.academicPeriod', fn (Builder $periodQuery) => $periodQuery->where('name', 'like', $like))
                    ->orWhere('general_objective', 'like', $like)
                    ->orWhere('course_intent', 'like', $like)
                    ->orWhere('general_observations', 'like', $like);
            });
        }

        if ($statusCode !== '') {
            $query->whereHas('status', fn (Builder $statusQuery) => $statusQuery->where('code', $statusCode));
        }

        if ($careerId > 0) {
            $query->whereHas('assignment.offering.group', fn (Builder $groupQuery) => $groupQuery->where('career_id', $careerId));
        }

        if ($periodId > 0) {
            $query->whereHas('assignment.offering.group', fn (Builder $groupQuery) => $groupQuery->where('academic_period_id', $periodId));
        }

        if ($dateFrom !== '') {
            $query->whereDate('updated_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('updated_at', '<=', $dateTo);
        }

        return $query;
    }

    protected function sortTeacherPlans(Collection $plans, string $sort): Collection
    {
        $sort = $this->sanitizeTeacherSort($sort);

        return match ($sort) {
            'oldest' => $plans->sortBy(fn (DidacticPlan $plan) => $plan->updated_at?->getTimestamp() ?? 0)->values(),
            'status' => $plans->sortBy(fn (DidacticPlan $plan) => sprintf(
                '%02d-%s',
                (int) ($plan->status?->sort_order ?? 99),
                $plan->assignment?->offering?->careerSubject?->subject?->name ?? ''
            ))->values(),
            'subject' => $plans->sortBy(fn (DidacticPlan $plan) => $plan->assignment?->offering?->careerSubject?->subject?->name ?? '')->values(),
            'submitted' => $plans->sortByDesc(fn (DidacticPlan $plan) => $plan->submitted_at?->getTimestamp() ?? 0)->values(),
            default => $plans->sortByDesc(fn (DidacticPlan $plan) => $plan->updated_at?->getTimestamp() ?? 0)->values(),
        };
    }

    protected function sanitizeTeacherStatus(string $statusCode): string
    {
        $allowed = array_map(
            static fn (PlanningStatusCode $statusCode) => $statusCode->value,
            PlanningStatusCode::cases(),
        );

        return in_array($statusCode, $allowed, true) ? $statusCode : '';
    }

    protected function sanitizeTeacherSort(string $sort): string
    {
        return in_array($sort, ['updated', 'oldest', 'status', 'subject', 'submitted'], true)
            ? $sort
            : 'updated';
    }

    protected function teacherSortOptions(): array
    {
        return [
            ['code' => 'updated', 'name' => 'Mas recientes'],
            ['code' => 'submitted', 'name' => 'Mas recientemente enviadas'],
            ['code' => 'status', 'name' => 'Por estatus'],
            ['code' => 'subject', 'name' => 'Por asignatura'],
            ['code' => 'oldest', 'name' => 'Mas antiguas'],
        ];
    }

    protected function teacherMetrics(Collection $plans): array
    {
        $counts = [];

        foreach ($plans as $plan) {
            $code = $plan->status?->code;

            if ($code !== null) {
                $counts[$code] = ($counts[$code] ?? 0) + 1;
            }
        }

        return [
            [
                'label' => 'Total',
                'value' => $plans->count(),
                'caption' => 'Planeaciones registradas en tu biblioteca.',
            ],
            [
                'label' => 'Borradores',
                'value' => $counts[PlanningStatusCode::DRAFT->value] ?? 0,
                'caption' => 'Aun puedes editarlas libremente.',
            ],
            [
                'label' => 'En revision',
                'value' => ($counts[PlanningStatusCode::SUBMITTED->value] ?? 0) + ($counts[PlanningStatusCode::UNDER_REVIEW->value] ?? 0),
                'caption' => 'Ya estan en el circuito de validacion.',
            ],
            [
                'label' => 'Autorizadas',
                'value' => $counts[PlanningStatusCode::AUTHORIZED->value] ?? 0,
                'caption' => 'Cierres exitosos del proceso.',
            ],
        ];
    }

    protected function teacherStatusOptions(): array
    {
        return PlanningStatus::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PlanningStatus $status) => [
                'code' => $status->code,
                'name' => $status->name,
            ])
            ->all();
    }

    protected function teacherCareerOptions(Collection $plans): array
    {
        return $plans
            ->map(function (DidacticPlan $plan): ?array {
                $career = $plan->assignment?->offering?->group?->career;

                if ($career === null) {
                    return null;
                }

                return [
                    'id' => $career->id,
                    'name' => $career->name,
                ];
            })
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values()
            ->all();
    }

    protected function teacherPeriodOptions(Collection $plans): array
    {
        return $plans
            ->map(function (DidacticPlan $plan): ?array {
                $period = $plan->assignment?->offering?->group?->academicPeriod;

                if ($period === null) {
                    return null;
                }

                return [
                    'id' => $period->id,
                    'name' => $period->name,
                ];
            })
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values()
            ->all();
    }

    protected function teacherStateHint(DidacticPlan $plan): string
    {
        return match ($plan->status?->code) {
            PlanningStatusCode::DRAFT->value => 'Borrador editable. Cuando este lista puedes enviarla a revision.',
            PlanningStatusCode::FEEDBACK->value => 'Tiene observaciones pendientes. Ajustala y vuelve a enviarla.',
            PlanningStatusCode::SUBMITTED->value => 'Ya fue enviada y esta esperando atencion del revisor.',
            PlanningStatusCode::UNDER_REVIEW->value => 'La revision tecnica esta en curso.',
            PlanningStatusCode::AUTHORIZED->value => 'La planeacion ya fue autorizada.',
            PlanningStatusCode::REJECTED->value => 'La planeacion fue cerrada con rechazo.',
            default => 'Consulta el detalle para revisar el estado actual.',
        };
    }
}
