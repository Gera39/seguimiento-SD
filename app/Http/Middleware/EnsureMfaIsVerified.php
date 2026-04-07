<?php

namespace App\Http\Middleware;

use App\Domain\Security\Mfa\MfaSessionService;
use App\Domain\Security\Mfa\UserMfaManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMfaIsVerified
{
    public function __construct(
        protected UserMfaManager $mfaManager,
        protected MfaSessionService $mfaSessionService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($this->mfaManager->primaryMethodFor($user) === null) {
            if (! $this->mfaSessionService->isVerified($request)) {
                $this->mfaSessionService->primeVerifiedSession($request);
            }

            return $next($request);
        }

        if ($this->mfaSessionService->isVerified($request)) {
            return $next($request);
        }

        return redirect()->route('mfa.challenge.show');
    }
}
