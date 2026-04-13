<?php

namespace App\Http\Controllers\Planning;

use App\Domain\Planning\Services\DidacticPlanCommentSnapshotService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\RespondDidacticPlanReviewCommentRequest;
use App\Models\DidacticPlan;
use App\Models\DidacticPlanReviewComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DidacticPlanReviewCommentController extends Controller
{
    public function __construct(
        protected DidacticPlanCommentSnapshotService $snapshotService,
    ) {
    }

    public function respond(
        RespondDidacticPlanReviewCommentRequest $request,
        DidacticPlan $didacticPlan,
        DidacticPlanReviewComment $comment,
    ): RedirectResponse {
        $didacticPlan->loadMissing([
            'status',
            'assignment',
            'units.modules',
            'evaluationCriteria',
            'reviews.comments',
        ]);

        abort_unless(
            $comment->review?->didactic_plan_id === $didacticPlan->id,
            404,
        );

        $this->authorize('update', $didacticPlan);

        $comment->fill([
            'teacher_response' => $request->string('teacher_response')->toString(),
            'teacher_responded_by_user_id' => $request->user()->id,
            'teacher_responded_at' => now(),
            'updated_value_snapshot' => $this->snapshotService->capture($didacticPlan, $comment->field_path),
            'comment_status_code' => 'ADDRESSED',
        ]);
        $comment->save();

        return redirect()
            ->route('plans.show', $didacticPlan)
            ->with('status', 'La respuesta al comentario se guardo y quedo marcada como atendida.');
    }

    public function resolve(
        Request $request,
        DidacticPlan $didacticPlan,
        DidacticPlanReviewComment $comment,
    ): RedirectResponse {
        $this->guardReviewerCommentAction($request, $didacticPlan, $comment);

        $comment->fill([
            'comment_status_code' => 'RESOLVED',
            'is_resolved' => true,
            'resolved_by_user_id' => $request->user()->id,
            'resolved_at' => now(),
            'validated_by_user_id' => $request->user()->id,
            'validated_at' => now(),
        ]);
        $comment->save();

        return redirect()
            ->route('plans.review.show', $didacticPlan)
            ->with('status', 'La observacion quedo validada como resuelta.');
    }

    public function reopen(
        Request $request,
        DidacticPlan $didacticPlan,
        DidacticPlanReviewComment $comment,
    ): RedirectResponse {
        $this->guardReviewerCommentAction($request, $didacticPlan, $comment);

        $comment->fill([
            'comment_status_code' => 'REOPENED',
            'is_resolved' => false,
            'resolved_by_user_id' => null,
            'resolved_at' => null,
            'validated_by_user_id' => null,
            'validated_at' => null,
        ]);
        $comment->save();

        return redirect()
            ->route('plans.review.show', $didacticPlan)
            ->with('status', 'La observacion se reabrio para una nueva correccion.');
    }

    protected function guardReviewerCommentAction(
        Request $request,
        DidacticPlan $didacticPlan,
        DidacticPlanReviewComment $comment,
    ): void {
        $didacticPlan->loadMissing([
            'status',
            'assignment.offering.group.career',
        ]);

        abort_unless(
            $comment->review?->didactic_plan_id === $didacticPlan->id,
            404,
        );

        $this->authorize('view', $didacticPlan);
        abort_unless($request->user()?->hasRole(\App\Domain\Security\Enums\RoleCode::REVISOR), 403);
    }
}
