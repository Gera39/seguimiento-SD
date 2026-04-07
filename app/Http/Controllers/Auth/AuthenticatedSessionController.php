<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Domain\Security\Authentication\AuthLoginAuditService;
use App\Domain\Security\Authentication\PostLoginRedirector;
use App\Domain\Security\Mfa\MfaSessionService;
use App\Domain\Security\Mfa\UserMfaManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        protected PostLoginRedirector $redirector,
        protected UserMfaManager $mfaManager,
        protected MfaSessionService $mfaSessionService,
        protected AuthLoginAuditService $auditService,
    ) {
    }

    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $fallbackUrl = $this->redirectPathFor($request);

        if ($user !== null) {
            $this->auditService->record($request, 'PASSWORD_LOGIN', true, $user);

            $mfaMethod = $this->mfaManager->primaryMethodFor($user);

            if ($mfaMethod !== null) {
                $this->mfaSessionService->beginChallenge($request, $mfaMethod, $fallbackUrl);
                $this->auditService->record($request, 'MFA_REQUIRED', true, $user, null, $mfaMethod);

                return redirect()->route('mfa.challenge.show');
            }

            $this->mfaSessionService->markVerified($request, $user);
        }

        return redirect()->intended($fallbackUrl);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->mfaSessionService->clear($request);
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function redirectPathFor(Request $request): string
    {
        return $request->user() !== null
            ? $this->redirector->for($request->user())
            : route('dashboard', absolute: false);
    }
}
