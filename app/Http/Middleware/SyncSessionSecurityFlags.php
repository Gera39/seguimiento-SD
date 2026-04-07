<?php

namespace App\Http\Middleware;

use App\Domain\Security\Mfa\MfaSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SyncSessionSecurityFlags
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (config('session.driver') !== 'database' || ! $request->hasSession()) {
            return;
        }

        $sessionId = $request->session()->getId();

        if (blank($sessionId)) {
            return;
        }

        try {
            DB::table((string) config('session.table', 'sessions'))
                ->where('id', $sessionId)
                ->update([
                    'is_mfa_verified' => (bool) $request->session()->get(MfaSessionService::VERIFIED_KEY, false),
                    'last_mfa_passed_at' => $request->session()->get(MfaSessionService::VERIFIED_AT_KEY),
                ]);
        } catch (Throwable) {
            // Avoid breaking the request lifecycle if the backing session store changes.
        }
    }
}
