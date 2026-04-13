<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Security\Authentication\AuthLoginAuditService;
use App\Domain\Security\Authentication\PostLoginRedirector;
use App\Domain\Security\Mfa\MfaSessionService;
use App\Domain\Security\Mfa\RecoveryCodeService;
use App\Domain\Security\Mfa\TotpService;
use App\Domain\Security\Mfa\UserMfaManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RedeemRecoveryCodeRequest;
use App\Http\Requests\Auth\VerifyTotpChallengeRequest;
use App\Models\UserMfaMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MfaChallengeController extends Controller
{
    public function __construct(
        protected UserMfaManager $mfaManager,
        protected MfaSessionService $mfaSessionService,
        protected TotpService $totpService,
        protected RecoveryCodeService $recoveryCodeService,
        protected PostLoginRedirector $redirector,
        protected AuthLoginAuditService $auditService,
    ) {
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return Redirect::route('login');
        }

        $method = $this->resolveChallengeMethod($request);

        if ($method === null) {
            $this->mfaSessionService->markVerified($request, $user);

            return redirect()->to($this->mfaSessionService->intendedUrl($request, $this->redirector->for($user)));
        }

        if ($this->mfaSessionService->isVerified($request)) {
            return redirect()->to($this->mfaSessionService->intendedUrl($request, $this->redirector->for($user)));
        }

        return Inertia::render('Auth/MfaChallenge', [
            'challenge' => [
                'label' => $method->label,
                'type' => $method->method_type,
                'recovery_codes_remaining' => $method->recoveryCodes()->whereNull('used_at')->count(),
            ],
            'status' => session('status'),
        ]);
    }

    public function store(VerifyTotpChallengeRequest $request): RedirectResponse
    {
        $user = $request->user();
        $method = $this->resolveChallengeMethod($request);

        if ($user === null || $method === null) {
            return redirect()->route('login');
        }

        $secret = Crypt::decryptString($method->secret_encrypted);

        if (! $this->totpService->verify($secret, $request->string('code')->toString())) {
            $this->auditService->record($request, 'MFA_FAILED', false, $user, 'invalid_totp_code', $method);

            throw ValidationException::withMessages([
                'code' => 'El codigo de verificacion no es valido.',
            ]);
        }

        $method->forceFill([
            'last_used_at' => now(),
        ])->save();

        $this->mfaSessionService->markVerified($request, $user, $method);
        $this->auditService->record($request, 'MFA_SUCCESS', true, $user, null, $method);

        return redirect()->to($this->mfaSessionService->intendedUrl($request, $this->redirector->for($user)));
    }

    public function storeRecoveryCode(RedeemRecoveryCodeRequest $request): RedirectResponse
    {
        $user = $request->user();
        $method = $this->resolveChallengeMethod($request);

        if ($user === null || $method === null) {
            return redirect()->route('login');
        }

        if (! $this->recoveryCodeService->redeem($method, $request->string('recovery_code')->toString())) {
            $this->auditService->record($request, 'MFA_FAILED', false, $user, 'invalid_recovery_code', $method);

            throw ValidationException::withMessages([
                'recovery_code' => 'El codigo de recuperacion no es valido o ya fue utilizado.',
            ]);
        }

        $method->forceFill([
            'last_used_at' => now(),
        ])->save();

        $this->mfaSessionService->markVerified($request, $user, $method);
        $this->auditService->record($request, 'MFA_SUCCESS', true, $user, null, $method);

        return redirect()->to($this->mfaSessionService->intendedUrl($request, $this->redirector->for($user)));
    }

    protected function resolveChallengeMethod(Request $request): ?UserMfaMethod
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        $methodId = $this->mfaSessionService->pendingMethodId($request);

        if ($methodId !== null) {
            return $user->mfaMethods()
                ->whereKey($methodId)
                ->where('is_active', true)
                ->whereNotNull('confirmed_at')
                ->first();
        }

        return $this->mfaManager->primaryMethodFor($user);
    }
}
