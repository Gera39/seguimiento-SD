<?php

namespace App\Domain\Security\Authentication;

use App\Models\AuthLoginAudit;
use App\Models\User;
use App\Models\UserMfaMethod;
use Illuminate\Http\Request;

class AuthLoginAuditService
{
    public function record(
        Request $request,
        string $eventType,
        bool $isSuccess,
        ?User $user = null,
        ?string $failureReason = null,
        ?UserMfaMethod $method = null,
    ): void {
        AuthLoginAudit::query()->create([
            'user_id' => $user?->id,
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'mfa_method_id' => $method?->id,
            'event_type' => $eventType,
            'is_success' => $isSuccess,
            'failure_reason' => $failureReason,
            'ip_address' => $request->ip(),
            'user_agent' => str($request->userAgent())->limit(500)->toString(),
            'occurred_at' => now(),
        ]);
    }
}
