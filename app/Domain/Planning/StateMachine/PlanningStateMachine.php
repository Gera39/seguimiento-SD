<?php

namespace App\Domain\Planning\StateMachine;

use App\Domain\Planning\Enums\PlanningStatusCode;
use App\Domain\Planning\Exceptions\InvalidPlanningTransitionException;
use App\Models\DidacticPlan;
use App\Models\DidacticPlanStatusHistory;
use App\Models\PlanningStatus;
use App\Models\PlanningTransitionRule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlanningStateMachine
{
    public function canTransition(
        User $user,
        DidacticPlan $plan,
        PlanningStatusCode|string $targetStatus,
    ): bool {
        return $this->resolveTransitionRule($user, $plan, $targetStatus) !== null;
    }

    public function transition(
        User $user,
        DidacticPlan $plan,
        PlanningStatusCode|string $targetStatus,
        ?string $comments = null,
    ): DidacticPlan {
        $transitionRule = $this->resolveTransitionRule($user, $plan, $targetStatus);

        if ($transitionRule === null) {
            throw new InvalidPlanningTransitionException('The requested planning transition is not allowed.');
        }

        if ($transitionRule->requires_comment && blank($comments)) {
            throw new InvalidPlanningTransitionException('A comment is required for this planning transition.');
        }

        $currentStatusId = $plan->status_id;

        return DB::transaction(function () use ($user, $plan, $transitionRule, $comments, $currentStatusId) {
            $this->applyTransitionSideEffects($user, $plan, $transitionRule);

            $plan->status()->associate($transitionRule->toStatus);
            $plan->updated_by_user_id = $user->id;
            $plan->save();

            DidacticPlanStatusHistory::create([
                'didactic_plan_id' => $plan->id,
                'from_status_id' => $currentStatusId,
                'to_status_id' => $transitionRule->to_status_id,
                'transition_rule_id' => $transitionRule->id,
                'changed_by_user_id' => $user->id,
                'comments' => $comments,
                'changed_at' => now(),
            ]);

            return $plan->fresh(['status']);
        });
    }

    protected function resolveTransitionRule(
        User $user,
        DidacticPlan $plan,
        PlanningStatusCode|string $targetStatus,
    ): ?PlanningTransitionRule {
        $targetCode = $targetStatus instanceof PlanningStatusCode ? $targetStatus->value : $targetStatus;

        $roleCodes = $user->activeRoleAssignments()
            ->with('role:id,code')
            ->get()
            ->pluck('role.code')
            ->filter()
            ->values()
            ->all();

        if ($roleCodes === []) {
            return null;
        }

        $target = PlanningStatus::query()
            ->where('code', $targetCode)
            ->first();

        if ($target === null) {
            return null;
        }

        return PlanningTransitionRule::query()
            ->with('toStatus')
            ->where('from_status_id', $plan->status_id)
            ->where('to_status_id', $target->id)
            ->whereHas('triggeredByRole', fn ($query) => $query->whereIn('code', $roleCodes))
            ->first();
    }

    protected function applyTransitionSideEffects(
        User $user,
        DidacticPlan $plan,
        PlanningTransitionRule $transitionRule,
    ): void {
        $targetCode = $transitionRule->toStatus->code;

        if ($targetCode === PlanningStatusCode::SUBMITTED->value) {
            $plan->submitted_at = now();
            $plan->locked_at = now();
            $plan->current_review_round = $plan->current_review_round + 1;
        }

        if ($targetCode === PlanningStatusCode::UNDER_REVIEW->value) {
            $plan->locked_at = now();
        }

        if ($targetCode === PlanningStatusCode::FEEDBACK->value) {
            $plan->feedback_released_at = now();
            $plan->locked_at = null;
        }

        if ($targetCode === PlanningStatusCode::AUTHORIZED->value) {
            $plan->authorized_at = now();
            $plan->authorized_by_user_id = $user->id;
            $plan->locked_at = now();
        }

        if ($targetCode === PlanningStatusCode::REJECTED->value) {
            $plan->locked_at = now();
        }
    }
}
