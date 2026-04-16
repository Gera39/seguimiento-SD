<?php

namespace App\Http\Controllers\Planning;

use App\Domain\Planning\Enums\PlanningStatusCode;
use App\Domain\Security\Enums\RoleCode;
use App\Http\Controllers\Controller;
use App\Models\DidacticPlan;
use App\Models\DidacticPlanReview;
use App\Models\PlanningStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class PlanningInboxController extends Controller
{
    public function reviewerDashboard(Request $request): Response
    {
        $payload = $this->buildQueuePayload($request, RoleCode::REVISOR, 'demo.validaciones');
        $plans = collect($payload['plans']);

        return Inertia::render('PanelRevisor', [
            'metrics' => $payload['metrics'],
            'queuePreview' => $plans
                ->whereIn('statusCode', [PlanningStatusCode::SUBMITTED->value, PlanningStatusCode::UNDER_REVIEW->value])
                ->take(5)
                ->values()
                ->all(),
            'recentOutcomes' => $plans
                ->whereIn('statusCode', [PlanningStatusCode::FEEDBACK->value, PlanningStatusCode::AUTHORIZED->value])
                ->take(5)
                ->values()
                ->all(),
            'queueUrl' => $payload['baseUrl'],
            'status' => session('status'),
        ]);
    }

    public function reviewerQueue(Request $request): Response
    {
        return Inertia::render('ValidacionSecuencias', [
            ...$this->buildQueuePayload($request, RoleCode::REVISOR, 'demo.validaciones'),
            'headline' => [
                'eyebrow' => 'Revision academica',
                'title' => 'Bandeja de validacion tecnica',
                'copy' => 'Consulta las planeaciones enviadas, filtra por estatus y entra directo a la revision tecnica.',
            ],
            'status' => session('status'),
        ]);
    }

    public function directorDashboard(Request $request): Response
    {
        $baseRouteName = $request->route()?->getName() === 'panel.coordinacion'
            ? 'panel.coordinacion'
            : 'panel.director';

        return Inertia::render('PanelDirector', [
            ...$this->buildQueuePayload($request, RoleCode::DIRECTIVO, $baseRouteName),
            'headline' => [
                'eyebrow' => 'Direccion academica',
                'title' => 'Bandeja de validacion final',
                'copy' => 'Supervisa el pipeline completo y autoriza las planeaciones que ya concluyeron la revision tecnica.',
            ],
            'status' => session('status'),
        ]);
    }

    protected function buildQueuePayload(Request $request, RoleCode $role, string $baseRouteName): array
    {
        $allowedStatuses = $this->allowedStatusesFor($role);
        $baseQuery = $this->applyRoleVisibility($this->baseQueueQuery(), $request->user(), $role);
        $queryWithoutStatus = $this->applyFilters(clone $baseQuery, $request, $allowedStatuses, false);
        $queryWithStatus = $this->applyFilters(clone $baseQuery, $request, $allowedStatuses, true);

        $allVisiblePlans = $this->sortPlans(
            $queryWithoutStatus->get(),
            $role,
        );

        $filteredPlans = $this->sortPlans(
            $queryWithStatus->get(),
            $role,
        )->map(fn (DidacticPlan $plan) => $this->serializeInboxPlan($plan, $role))
            ->values()
            ->all();

        return [
            'baseUrl' => route($baseRouteName, absolute: false),
            'filters' => [
                'search' => $request->string('search')->trim()->toString(),
                'status' => $this->sanitizeStatusFilter($request->string('status')->trim()->toString(), $allowedStatuses),
                'career' => $request->integer('career') ?: null,
                'period' => $request->integer('period') ?: null,
                'teacher' => $request->string('teacher')->trim()->toString(),
                'subject' => $request->string('subject')->trim()->toString(),
                'group' => $request->string('group')->trim()->toString(),
                'review_round' => $request->integer('review_round') ?: null,
                'has_open_comments' => $request->boolean('has_open_comments'),
                'date_from' => $request->string('date_from')->trim()->toString(),
                'date_to' => $request->string('date_to')->trim()->toString(),
                'sort' => $this->sanitizeSort($request->string('sort')->trim()->toString()),
            ],
            'statusOptions' => $this->statusOptions($allowedStatuses),
            'careerOptions' => $this->careerOptions($allVisiblePlans),
            'periodOptions' => $this->periodOptions($allVisiblePlans),
            'sortOptions' => $this->sortOptions(),
            'metrics' => $this->metricsFor($role, $this->statusCounts($allVisiblePlans)),
            'summary' => [
                'totalVisible' => $allVisiblePlans->count(),
                'filtered' => count($filteredPlans),
            ],
            'statusBreakdown' => $this->statusBreakdown($allVisiblePlans, $allowedStatuses),
            'dashboard' => $role === RoleCode::DIRECTIVO ? $this->directorInsights($allVisiblePlans) : null,
            'plans' => $filteredPlans,
        ];
    }

    protected function baseQueueQuery(): Builder
    {
        return DidacticPlan::query()
            ->with([
                'status',
                'assignment.teacher',
                'assignment.offering.group.career',
                'assignment.offering.group.academicPeriod',
                'assignment.offering.careerSubject.subject',
                'reviews.comments',
                'reviews.reviewer',
                'authorizer',
                'statusHistory.toStatus',
            ])
            ->whereHas('status', fn (Builder $query) => $query->where('code', '!=', PlanningStatusCode::DRAFT->value));
    }

    protected function applyRoleVisibility(Builder $query, ?\App\Models\User $user, RoleCode $role): Builder
    {
        if ($user === null || $role !== RoleCode::REVISOR || $user->hasGlobalReviewerScope()) {
            return $query;
        }

        $careerIds = $user->reviewerCareerIds()->all();

        if ($careerIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas(
            'assignment.offering.group',
            fn (Builder $groupQuery) => $groupQuery->whereIn('career_id', $careerIds),
        );
    }

    protected function applyFilters(
        Builder $query,
        Request $request,
        array $allowedStatuses,
        bool $includeStatusFilter,
    ): Builder {
        $search = $request->string('search')->trim()->toString();
        $careerId = $request->integer('career');
        $periodId = $request->integer('period');
        $teacher = $request->string('teacher')->trim()->toString();
        $subject = $request->string('subject')->trim()->toString();
        $group = $request->string('group')->trim()->toString();
        $reviewRound = $request->integer('review_round');
        $hasOpenComments = $request->boolean('has_open_comments');
        $dateFrom = $request->string('date_from')->trim()->toString();
        $dateTo = $request->string('date_to')->trim()->toString();
        $statusCode = $this->sanitizeStatusFilter($request->string('status')->trim()->toString(), $allowedStatuses);

        if ($search !== '') {
            $like = '%'.$search.'%';

            $query->where(function (Builder $builder) use ($like): void {
                $builder
                    ->where('plan_folio', 'like', $like)
                    ->orWhereHas('assignment.teacher', fn (Builder $teacherQuery) => $teacherQuery->where('name', 'like', $like))
                    ->orWhereHas('assignment.offering.group', fn (Builder $groupQuery) => $groupQuery->where('group_code', 'like', $like))
                    ->orWhereHas('assignment.offering.careerSubject.subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', $like))
                    ->orWhereHas('assignment.offering.group.career', fn (Builder $careerQuery) => $careerQuery->where('name', 'like', $like))
                    ->orWhereHas('assignment.offering.group.academicPeriod', fn (Builder $periodQuery) => $periodQuery->where('name', 'like', $like))
                    ->orWhereHas('reviews', fn (Builder $reviewQuery) => $reviewQuery->where('general_comments', 'like', $like))
                    ->orWhere('submission_notes', 'like', $like);
            });
        }

        if ($teacher !== '') {
            $query->whereHas('assignment.teacher', fn (Builder $teacherQuery) => $teacherQuery->where('name', 'like', '%'.$teacher.'%'));
        }

        if ($subject !== '') {
            $query->whereHas('assignment.offering.careerSubject.subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', '%'.$subject.'%'));
        }

        if ($group !== '') {
            $query->whereHas('assignment.offering.group', fn (Builder $groupQuery) => $groupQuery->where('group_code', 'like', '%'.$group.'%'));
        }

        if ($careerId > 0) {
            $query->whereHas('assignment.offering.group', fn (Builder $groupQuery) => $groupQuery->where('career_id', $careerId));
        }

        if ($periodId > 0) {
            $query->whereHas('assignment.offering.group', fn (Builder $groupQuery) => $groupQuery->where('academic_period_id', $periodId));
        }

        if ($reviewRound > 0) {
            $query->where('current_review_round', $reviewRound);
        }

        if ($hasOpenComments) {
            $query->whereHas('reviews.comments', fn (Builder $commentQuery) => $commentQuery->where('is_resolved', false));
        }

        if ($dateFrom !== '') {
            $query->whereDate('updated_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('updated_at', '<=', $dateTo);
        }

        if ($includeStatusFilter && $statusCode !== '') {
            $query->whereHas('status', fn (Builder $statusQuery) => $statusQuery->where('code', $statusCode));
        }

        return $query;
    }

    protected function sanitizeStatusFilter(string $statusCode, array $allowedStatuses): string
    {
        return in_array($statusCode, $allowedStatuses, true) ? $statusCode : '';
    }

    protected function sortPlans(Collection $plans, RoleCode $role): Collection
    {
        return $this->sortPlansBy(
            $plans,
            $role,
            request()->string('sort')->trim()->toString(),
        );
    }

    protected function sortPlansBy(Collection $plans, RoleCode $role, string $sort): Collection
    {
        $sort = $this->sanitizeSort($sort);

        if ($sort === 'newest') {
            return $plans->sortByDesc(fn (DidacticPlan $plan) => ($plan->updated_at ?? $plan->submitted_at)?->getTimestamp() ?? 0)->values();
        }

        if ($sort === 'oldest') {
            return $plans->sortBy(fn (DidacticPlan $plan) => ($plan->updated_at ?? $plan->submitted_at)?->getTimestamp() ?? 0)->values();
        }

        if ($sort === 'teacher') {
            return $plans->sortBy(fn (DidacticPlan $plan) => $plan->assignment?->teacher?->name ?? '')->values();
        }

        if ($sort === 'comments') {
            return $plans->sortByDesc(function (DidacticPlan $plan): int {
                return $plan->reviews
                    ->flatMap(fn (DidacticPlanReview $review) => $review->comments)
                    ->where('is_resolved', false)
                    ->count();
            })->values();
        }

        return $plans
            ->sortBy(function (DidacticPlan $plan) use ($role): string {
                $priority = $this->statusPriority($role, $plan->status?->code);
                $timestamp = ($plan->submitted_at ?? $plan->updated_at)?->getTimestamp() ?? 0;

                return sprintf('%02d-%010d', $priority, 9_999_999_999 - $timestamp);
            })
            ->values();
    }

    protected function sanitizeSort(string $sort): string
    {
        return in_array($sort, ['priority', 'newest', 'oldest', 'teacher', 'comments'], true)
            ? $sort
            : 'priority';
    }

    protected function statusPriority(RoleCode $role, ?string $statusCode): int
    {
        $priorityMap = match ($role) {
            RoleCode::DIRECTIVO => [
                PlanningStatusCode::UNDER_REVIEW->value => 1,
                PlanningStatusCode::AUTHORIZED->value => 2,
                PlanningStatusCode::SUBMITTED->value => 3,
                PlanningStatusCode::FEEDBACK->value => 4,
                PlanningStatusCode::REJECTED->value => 5,
            ],
            default => [
                PlanningStatusCode::SUBMITTED->value => 1,
                PlanningStatusCode::UNDER_REVIEW->value => 2,
                PlanningStatusCode::FEEDBACK->value => 3,
                PlanningStatusCode::AUTHORIZED->value => 4,
                PlanningStatusCode::REJECTED->value => 5,
            ],
        };

        return $priorityMap[$statusCode] ?? 99;
    }

    protected function allowedStatusesFor(RoleCode $role): array
    {
        return match ($role) {
            RoleCode::DIRECTIVO => [
                PlanningStatusCode::UNDER_REVIEW->value,
                PlanningStatusCode::AUTHORIZED->value,
                PlanningStatusCode::SUBMITTED->value,
                PlanningStatusCode::FEEDBACK->value,
                PlanningStatusCode::REJECTED->value,
            ],
            default => [
                PlanningStatusCode::SUBMITTED->value,
                PlanningStatusCode::UNDER_REVIEW->value,
                PlanningStatusCode::FEEDBACK->value,
                PlanningStatusCode::AUTHORIZED->value,
                PlanningStatusCode::REJECTED->value,
            ],
        };
    }

    protected function statusOptions(array $allowedStatuses): array
    {
        return PlanningStatus::query()
            ->whereIn('code', $allowedStatuses)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PlanningStatus $status) => [
                'code' => $status->code,
                'name' => $status->name,
            ])
            ->values()
            ->all();
    }

    protected function careerOptions(Collection $plans): array
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

    protected function periodOptions(Collection $plans): array
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

    protected function statusCounts(Collection $plans): array
    {
        $counts = [];

        foreach ($plans as $plan) {
            $code = $plan->status?->code;

            if ($code === null) {
                continue;
            }

            $counts[$code] = ($counts[$code] ?? 0) + 1;
        }

        return $counts;
    }

    protected function sortOptions(): array
    {
        return [
            ['code' => 'priority', 'name' => 'Prioridad operativa'],
            ['code' => 'newest', 'name' => 'Movimiento mas reciente'],
            ['code' => 'oldest', 'name' => 'Movimiento mas antiguo'],
            ['code' => 'teacher', 'name' => 'Docente A-Z'],
            ['code' => 'comments', 'name' => 'Mas observaciones abiertas'],
        ];
    }

    protected function statusBreakdown(Collection $plans, array $allowedStatuses): array
    {
        $counts = $this->statusCounts($plans);
        $statusMap = PlanningStatus::query()
            ->whereIn('code', $allowedStatuses)
            ->get()
            ->keyBy('code');

        return collect($allowedStatuses)
            ->map(function (string $statusCode) use ($counts, $statusMap): array {
                $status = $statusMap->get($statusCode);

                return [
                    'code' => $statusCode,
                    'name' => $status?->name ?? $statusCode,
                    'count' => $counts[$statusCode] ?? 0,
                ];
            })
            ->values()
            ->all();
    }

    protected function directorInsights(Collection $plans): array
    {
        $byCareer = $plans
            ->groupBy(fn (DidacticPlan $plan) => $plan->assignment?->offering?->group?->career?->name ?? 'Sin carrera')
            ->map(function (Collection $careerPlans, string $careerName): array {
                return [
                    'career' => $careerName,
                    'total' => $careerPlans->count(),
                    'pending_final' => $careerPlans->where('status.code', PlanningStatusCode::UNDER_REVIEW->value)->count(),
                    'authorized' => $careerPlans->where('status.code', PlanningStatusCode::AUTHORIZED->value)->count(),
                ];
            })
            ->sortByDesc('pending_final')
            ->take(6)
            ->values()
            ->all();

        $pendingFinal = $plans
            ->where('status.code', PlanningStatusCode::UNDER_REVIEW->value)
            ->sortByDesc(fn (DidacticPlan $plan) => $plan->reviews->flatMap(fn (DidacticPlanReview $review) => $review->comments)->where('is_resolved', false)->count())
            ->take(5)
            ->map(fn (DidacticPlan $plan) => [
                'id' => $plan->id,
                'folio' => $plan->plan_folio,
                'subject' => $plan->assignment?->offering?->careerSubject?->subject?->name ?? 'Sin asignatura',
                'teacher' => $plan->assignment?->teacher?->name ?? 'Sin docente',
                'career' => $plan->assignment?->offering?->group?->career?->name ?? 'Sin carrera',
                'open_comments' => $plan->reviews->flatMap(fn (DidacticPlanReview $review) => $review->comments)->where('is_resolved', false)->count(),
                'review_round' => $plan->current_review_round,
                'url' => route('plans.final.show', $plan, absolute: false),
            ])
            ->values()
            ->all();

        $recentAuthorizations = $plans
            ->where('status.code', PlanningStatusCode::AUTHORIZED->value)
            ->sortByDesc(fn (DidacticPlan $plan) => $plan->authorized_at?->getTimestamp() ?? 0)
            ->take(5)
            ->map(fn (DidacticPlan $plan) => [
                'id' => $plan->id,
                'folio' => $plan->plan_folio,
                'subject' => $plan->assignment?->offering?->careerSubject?->subject?->name ?? 'Sin asignatura',
                'teacher' => $plan->assignment?->teacher?->name ?? 'Sin docente',
                'authorized_at' => optional($plan->authorized_at)->format('d/m/Y H:i'),
                'authorizer' => $plan->authorizer?->name ?? 'Sin responsable',
                'url' => route('plans.show', $plan, absolute: false),
            ])
            ->values()
            ->all();

        return [
            'byCareer' => $byCareer,
            'pendingFinal' => $pendingFinal,
            'recentAuthorizations' => $recentAuthorizations,
        ];
    }

    protected function metricsFor(RoleCode $role, array $counts): array
    {
        return match ($role) {
            RoleCode::DIRECTIVO => [
                [
                    'label' => 'Pendientes final',
                    'value' => $counts[PlanningStatusCode::UNDER_REVIEW->value] ?? 0,
                    'caption' => 'Planeaciones listas para decision final.',
                ],
                [
                    'label' => 'Autorizadas',
                    'value' => $counts[PlanningStatusCode::AUTHORIZED->value] ?? 0,
                    'caption' => 'Cierres concluidos por direccion.',
                ],
                [
                    'label' => 'Con feedback',
                    'value' => $counts[PlanningStatusCode::FEEDBACK->value] ?? 0,
                    'caption' => 'Planeaciones devueltas al docente.',
                ],
                [
                    'label' => 'En espera tecnica',
                    'value' => $counts[PlanningStatusCode::SUBMITTED->value] ?? 0,
                    'caption' => 'Aun no concluyen la revision tecnica.',
                ],
            ],
            default => [
                [
                    'label' => 'Pendientes',
                    'value' => $counts[PlanningStatusCode::SUBMITTED->value] ?? 0,
                    'caption' => 'Solicitudes nuevas por dictaminar.',
                ],
                [
                    'label' => 'En revision',
                    'value' => $counts[PlanningStatusCode::UNDER_REVIEW->value] ?? 0,
                    'caption' => 'Planeaciones tomadas por un revisor.',
                ],
                [
                    'label' => 'Con feedback',
                    'value' => $counts[PlanningStatusCode::FEEDBACK->value] ?? 0,
                    'caption' => 'Regresaron al docente con observaciones.',
                ],
                [
                    'label' => 'Autorizadas',
                    'value' => $counts[PlanningStatusCode::AUTHORIZED->value] ?? 0,
                    'caption' => 'Historico de cierres exitosos.',
                ],
            ],
        };
    }

    protected function serializeInboxPlan(DidacticPlan $plan, RoleCode $role): array
    {
        $latestReview = $plan->reviews
            ->sortByDesc(fn (DidacticPlanReview $review) => $review->reviewed_at?->getTimestamp() ?? 0)
            ->first();

        $openComments = $plan->reviews
            ->flatMap(fn (DidacticPlanReview $review) => $review->comments)
            ->where('is_resolved', false)
            ->count();

        return [
            'id' => $plan->id,
            'folio' => $plan->plan_folio,
            'subject' => $plan->assignment?->offering?->careerSubject?->subject?->name ?? 'Sin asignatura',
            'teacher' => $plan->assignment?->teacher?->name ?? 'Sin docente',
            'career' => $plan->assignment?->offering?->group?->career?->name ?? 'Sin carrera',
            'group' => $plan->assignment?->offering?->group?->group_code ?? 'Sin grupo',
            'period' => $plan->assignment?->offering?->group?->academicPeriod?->name ?? 'Sin periodo',
            'statusCode' => $plan->status?->code,
            'statusName' => $plan->status?->name,
            'submittedAt' => optional($plan->submitted_at)->format('d/m/Y H:i'),
            'authorizedAt' => optional($plan->authorized_at)->format('d/m/Y H:i'),
            'updatedAt' => optional($plan->updated_at)->format('d/m/Y H:i'),
            'reviewRound' => $plan->current_review_round,
            'openComments' => $openComments,
            'latestStatusChangeAt' => optional($plan->statusHistory->sortByDesc('changed_at')->first()?->changed_at)->format('d/m/Y H:i'),
            'detailUrl' => route('plans.show', $plan, absolute: false),
            'reviewUrl' => route('plans.review.show', $plan, absolute: false),
            'finalUrl' => route('plans.final.show', $plan, absolute: false),
            'primaryAction' => $this->primaryActionFor($role, $plan),
            'latestReview' => $latestReview ? [
                'stage' => $latestReview->review_stage_code === 'FINAL' ? 'Validacion final' : 'Revision tecnica',
                'reviewer' => $latestReview->reviewer?->name ?? 'Sin revisor',
                'reviewedAt' => optional($latestReview->reviewed_at)->format('d/m/Y H:i'),
                'generalComments' => str($latestReview->general_comments)->squish()->limit(140)->toString(),
            ] : null,
        ];
    }

    protected function primaryActionFor(RoleCode $role, DidacticPlan $plan): array
    {
        $statusCode = $plan->status?->code;

        if ($role === RoleCode::DIRECTIVO) {
            return match ($statusCode) {
                PlanningStatusCode::UNDER_REVIEW->value => [
                    'label' => 'Validacion final',
                    'url' => route('plans.final.show', $plan, absolute: false),
                ],
                PlanningStatusCode::AUTHORIZED->value => [
                    'label' => 'Ver cierre',
                    'url' => route('plans.final.show', $plan, absolute: false),
                ],
                default => [
                    'label' => 'Ver detalle',
                    'url' => route('plans.show', $plan, absolute: false),
                ],
            };
        }

        return match ($statusCode) {
            PlanningStatusCode::SUBMITTED->value => [
                'label' => 'Iniciar revision',
                'url' => route('plans.review.show', $plan, absolute: false),
            ],
            PlanningStatusCode::UNDER_REVIEW->value => [
                'label' => 'Continuar revision',
                'url' => route('plans.review.show', $plan, absolute: false),
            ],
            PlanningStatusCode::FEEDBACK->value => [
                'label' => 'Ver feedback',
                'url' => route('plans.show', $plan, absolute: false),
            ],
            default => [
                'label' => 'Ver detalle',
                'url' => route('plans.show', $plan, absolute: false),
            ],
        };
    }
}
