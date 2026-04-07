<?php

namespace App\Http\Middleware;

use App\Domain\Security\Enums\RoleCode;
use App\Models\DidacticPlan;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanIsEditable
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routePlan = $request->route('didactic_plan') ?? $request->route('didacticPlan');

        if ($user === null || $routePlan === null) {
            return $next($request);
        }

        $plan = $routePlan instanceof DidacticPlan
            ? $routePlan->loadMissing('status')
            : DidacticPlan::query()->with('status')->findOrFail($routePlan);

        if ($user->hasRole(RoleCode::DOCENTE) && ! $plan->isTeacherEditable()) {
            abort(Response::HTTP_LOCKED, 'The didactic plan is locked because it is already in review.');
        }

        return $next($request);
    }
}
