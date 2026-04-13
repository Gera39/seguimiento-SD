<?php

namespace App\Http\Controllers\Planning;

use App\Domain\Planning\Enums\PlanningStatusCode;
use App\Domain\Planning\Enums\PlanningValidationContext;
use App\Domain\Planning\Services\DidacticPlanCommentSnapshotService;
use App\Domain\Planning\Services\DidacticPlanValidationService;
use App\Domain\Planning\StateMachine\PlanningStateMachine;
use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\AuthorizeDidacticPlanRequest;
use App\Http\Requests\Planning\FeedbackDidacticPlanRequest;
use App\Http\Requests\Planning\SubmitDidacticPlanRequest;
use App\Models\DidacticPlan;
use App\Models\DidacticPlanReview;
use App\Models\PlanningStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DidacticPlanTransitionController extends Controller
{
    public function __construct(
        protected PlanningStateMachine $stateMachine,
        protected DidacticPlanValidationService $validationService,
        protected DidacticPlanCommentSnapshotService $commentSnapshotService,
    ) {
    }

    public function showReview(DidacticPlan $didacticPlan): Response
    {
        $didacticPlan->loadMissing([
            'status',
            'assignment.teacher',
            'assignment.offering.careerSubject.subject',
            'assignment.offering.group.career',
            'units.modules',
            'evaluationCriteria',
            'reviews.comments',
        ]);

        $this->authorize('view', $didacticPlan);

        return Inertia::render('SecuenciaRevision', [
            'plan' => $this->serializeReviewPlan($didacticPlan),
            'status' => session('status'),
        ]);
    }

    public function showFinalValidation(DidacticPlan $didacticPlan): Response
    {
        $didacticPlan->loadMissing([
            'status',
            'assignment.teacher',
            'assignment.offering.careerSubject.subject',
            'assignment.offering.group.career',
            'validationSnapshots',
            'reviews.comments',
        ]);

        $this->authorize('view', $didacticPlan);

        return Inertia::render('ValidacionFinal', [
            'plan' => $this->serializeFinalPlan($didacticPlan),
            'status' => session('status'),
        ]);
    }

    public function submit(SubmitDidacticPlanRequest $request, DidacticPlan $didacticPlan): RedirectResponse
    {
        $didacticPlan->loadMissing('status', 'units.modules', 'evaluationCriteria');
        $this->authorize('submit', $didacticPlan);

        DB::transaction(function () use ($request, $didacticPlan): void {
            if ($request->filled('submission_notes')) {
                $didacticPlan->submission_notes = $request->string('submission_notes')->toString();
                $didacticPlan->updated_by_user_id = $request->user()->id;
                $didacticPlan->save();
            }

            $this->validationService->validateAndCapture($didacticPlan->fresh(['units.modules', 'evaluationCriteria']), PlanningValidationContext::SUBMISSION, $request->user());
            $this->stateMachine->transition($request->user(), $didacticPlan->fresh('status'), PlanningStatusCode::SUBMITTED, $request->input('submission_notes'));
        });

        return redirect()
            ->route('plans.show', $didacticPlan)
            ->with('status', 'La planeacion fue enviada a revision y quedo bloqueada para edicion.');
    }

    public function startReview(Request $request, DidacticPlan $didacticPlan): RedirectResponse
    {
        $didacticPlan->loadMissing('status');
        $this->authorize('startReview', $didacticPlan);

        $this->stateMachine->transition($request->user(), $didacticPlan, PlanningStatusCode::UNDER_REVIEW);

        return redirect()
            ->route('plans.review.show', $didacticPlan)
            ->with('status', 'La planeacion quedo oficialmente en revision.');
    }

    public function feedback(FeedbackDidacticPlanRequest $request, DidacticPlan $didacticPlan): RedirectResponse
    {
        $didacticPlan->loadMissing('status');
        $this->authorize('feedback', $didacticPlan);

        DB::transaction(function () use ($request, $didacticPlan): void {
            $plan = $didacticPlan;

            if ($plan->status?->code === PlanningStatusCode::SUBMITTED->value) {
                $plan = $this->stateMachine->transition($request->user(), $plan, PlanningStatusCode::UNDER_REVIEW);
            }

            $feedbackStatusId = PlanningStatus::query()
                ->where('code', PlanningStatusCode::FEEDBACK->value)
                ->value('id');

            $review = DidacticPlanReview::query()->create([
                'didactic_plan_id' => $plan->id,
                'review_round' => max($plan->current_review_round, 1),
                'review_stage_code' => 'TECHNICAL',
                'reviewer_user_id' => $request->user()->id,
                'decision_status_id' => $feedbackStatusId,
                'general_comments' => $request->string('general_comments')->toString(),
                'started_at' => now(),
                'reviewed_at' => now(),
            ]);

            foreach ($request->validated('review_comments') as $commentData) {
                $review->comments()->create([
                    ...$commentData,
                    'observed_value_snapshot' => $this->commentSnapshotService->capture($plan, $commentData['field_path'] ?? null),
                    'comment_status_code' => 'OPEN',
                ]);
            }

            $this->stateMachine->transition(
                $request->user(),
                $plan->fresh('status'),
                PlanningStatusCode::FEEDBACK,
                $request->string('general_comments')->toString(),
            );
        });

        return redirect()
            ->route('plans.show', $didacticPlan)
            ->with('status', 'La retroalimentacion fue registrada y la planeacion volvio al docente.');
    }

    public function authorizePlan(AuthorizeDidacticPlanRequest $request, DidacticPlan $didacticPlan): RedirectResponse
    {
        $didacticPlan->loadMissing('status', 'units.modules', 'evaluationCriteria');
        $this->authorize('authorize', $didacticPlan);

        DB::transaction(function () use ($request, $didacticPlan): void {
            $this->validationService->validateAndCapture(
                $didacticPlan->fresh(['units.modules', 'evaluationCriteria']),
                PlanningValidationContext::AUTHORIZATION,
                $request->user(),
            );

            $authorizedStatusId = PlanningStatus::query()
                ->where('code', PlanningStatusCode::AUTHORIZED->value)
                ->value('id');

            DidacticPlanReview::query()->create([
                'didactic_plan_id' => $didacticPlan->id,
                'review_round' => max($didacticPlan->current_review_round, 1),
                'review_stage_code' => 'FINAL',
                'reviewer_user_id' => $request->user()->id,
                'decision_status_id' => $authorizedStatusId,
                'general_comments' => $request->input('general_comments'),
                'started_at' => now(),
                'reviewed_at' => now(),
            ]);

            $this->stateMachine->transition(
                $request->user(),
                $didacticPlan->fresh('status'),
                PlanningStatusCode::AUTHORIZED,
                $request->input('general_comments'),
            );
        });

        return redirect()
            ->route('plans.show', $didacticPlan)
            ->with('status', 'La planeacion fue autorizada correctamente.');
    }

    protected function serializeReviewPlan(DidacticPlan $plan): array
    {
        return [
            'id' => $plan->id,
            'folio' => $plan->plan_folio,
            'title' => $plan->assignment?->offering?->careerSubject?->subject?->name,
            'teacher' => $plan->assignment?->teacher?->name,
            'career' => $plan->assignment?->offering?->group?->career?->name,
            'status' => $plan->status?->name,
            'statusCode' => $plan->status?->code,
            'generalObjective' => $plan->general_objective,
            'units' => $plan->units->count(),
            'modules' => $plan->units->sum(fn ($unit) => $unit->modules->count()),
            'canStartReview' => auth()->user()?->can('startReview', $plan) ?? false,
            'canFeedback' => auth()->user()?->can('feedback', $plan) ?? false,
            'startReviewUrl' => route('plans.start-review', $plan, absolute: false),
            'feedbackUrl' => route('plans.feedback', $plan, absolute: false),
            'detailUrl' => route('plans.show', $plan, absolute: false),
            'comments' => $plan->reviews
                ->flatMap(fn ($review) => $review->comments)
                ->map(fn ($comment) => [
                    'id' => $comment->id,
                    'entity_type' => $comment->entity_type,
                    'field_path' => $comment->field_path,
                    'field_label' => $comment->field_label,
                    'severity_code' => $comment->severity_code,
                    'comment_text' => $comment->comment_text,
                    'observed_value_snapshot' => $comment->observed_value_snapshot,
                    'teacher_response' => $comment->teacher_response,
                    'updated_value_snapshot' => $comment->updated_value_snapshot,
                    'comment_status_code' => $comment->comment_status_code,
                    'resolve_url' => auth()->user()?->hasRole(\App\Domain\Security\Enums\RoleCode::REVISOR)
                        ? route('plans.comments.resolve', ['didacticPlan' => $plan, 'comment' => $comment], absolute: false)
                        : null,
                    'reopen_url' => auth()->user()?->hasRole(\App\Domain\Security\Enums\RoleCode::REVISOR)
                        ? route('plans.comments.reopen', ['didacticPlan' => $plan, 'comment' => $comment], absolute: false)
                        : null,
                ])
                ->values()
                ->all(),
            'commentTargets' => $this->commentSnapshotService->commentTargets($plan),
        ];
    }

    protected function serializeFinalPlan(DidacticPlan $plan): array
    {
        $latestSnapshot = $plan->validationSnapshots->sortByDesc('created_at')->first();

        return [
            'id' => $plan->id,
            'folio' => $plan->plan_folio,
            'title' => $plan->assignment?->offering?->careerSubject?->subject?->name,
            'teacher' => $plan->assignment?->teacher?->name,
            'career' => $plan->assignment?->offering?->group?->career?->name,
            'status' => $plan->status?->name,
            'statusCode' => $plan->status?->code,
            'progress' => $latestSnapshot?->total_progress_percentage,
            'evaluation' => $latestSnapshot?->total_evaluation_percentage,
            'unitHours' => $latestSnapshot?->total_unit_hours,
            'moduleHours' => $latestSnapshot?->total_module_hours,
            'canAuthorize' => auth()->user()?->can('authorize', $plan) ?? false,
            'authorizeUrl' => route('plans.authorize', $plan, absolute: false),
            'feedbackSummary' => $plan->reviews
                ->where('review_stage_code', 'TECHNICAL')
                ->sortByDesc('reviewed_at')
                ->values()
                ->map(fn ($review) => [
                    'reviewed_at' => optional($review->reviewed_at)->format('d/m/Y H:i'),
                    'general_comments' => $review->general_comments,
                ])
                ->all(),
        ];
    }
}
