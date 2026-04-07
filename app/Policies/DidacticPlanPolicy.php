<?php

namespace App\Policies;

use App\Domain\Planning\Enums\PlanningStatusCode;
use App\Domain\Planning\StateMachine\PlanningStateMachine;
use App\Domain\Security\Enums\RoleCode;
use App\Models\DidacticPlan;
use App\Models\User;

class DidacticPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            RoleCode::ADMIN,
            RoleCode::DOCENTE,
            RoleCode::REVISOR,
            RoleCode::DIRECTIVO,
        ]);
    }

    public function view(User $user, DidacticPlan $plan): bool
    {
        if ($user->hasRole(RoleCode::ADMIN)) {
            return true;
        }

        if ($user->hasRole(RoleCode::DOCENTE)) {
            return (int) $plan->assignment?->teacher_user_id === (int) $user->id;
        }

        return $user->hasAnyRole([RoleCode::REVISOR, RoleCode::DIRECTIVO]);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(RoleCode::DOCENTE);
    }

    public function update(User $user, DidacticPlan $plan): bool
    {
        return $this->view($user, $plan)
            && $user->hasRole(RoleCode::DOCENTE)
            && $plan->isTeacherEditable();
    }

    public function delete(User $user, DidacticPlan $plan): bool
    {
        return $this->view($user, $plan)
            && $user->hasRole(RoleCode::DOCENTE)
            && (bool) ($plan->status?->is_teacher_deletable ?? false);
    }

    public function submit(User $user, DidacticPlan $plan): bool
    {
        return $this->view($user, $plan)
            && $user->hasRole(RoleCode::DOCENTE)
            && app(PlanningStateMachine::class)->canTransition($user, $plan, PlanningStatusCode::SUBMITTED);
    }

    public function startReview(User $user, DidacticPlan $plan): bool
    {
        return $this->view($user, $plan)
            && $user->hasRole(RoleCode::REVISOR)
            && app(PlanningStateMachine::class)->canTransition($user, $plan, PlanningStatusCode::UNDER_REVIEW);
    }

    public function feedback(User $user, DidacticPlan $plan): bool
    {
        if (! $this->view($user, $plan) || ! $user->hasRole(RoleCode::REVISOR)) {
            return false;
        }

        if ($plan->status?->code === PlanningStatusCode::SUBMITTED->value) {
            return $this->startReview($user, $plan);
        }

        return app(PlanningStateMachine::class)->canTransition($user, $plan, PlanningStatusCode::FEEDBACK);
    }

    public function authorize(User $user, DidacticPlan $plan): bool
    {
        return $this->view($user, $plan)
            && $user->hasRole(RoleCode::DIRECTIVO)
            && app(PlanningStateMachine::class)->canTransition($user, $plan, PlanningStatusCode::AUTHORIZED);
    }
}
