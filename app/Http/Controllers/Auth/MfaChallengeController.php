<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Security\Authentication\AuthLoginAuditService;
use App\Domain\Security\Mfa\EmailOtpService;
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
use Illuminate\Support\Facades\Auth;
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
        protected EmailOtpService $emailOtpService,
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

        if ($this->mfaSessionService->challengeExpired($request)) {
            return $this->abortExpiredChallenge($request, $user);
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
                'destination_masked' => $method->destination_masked,
                'recovery_codes_remaining' => $method->recoveryCodes()->whereNull('used_at')->count(),
                'expires_at' => $method->method_type === 'EMAIL_OTP' ? $this->emailOtpService->expiresAt($request) : null,
                'resend_available_at' => $method->method_type === 'EMAIL_OTP' ? $this->emailOtpService->resendAvailableAt($request) : null,
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

        if ($this->mfaSessionService->challengeExpired($request)) {
            return $this->abortExpiredChallenge($request, $user);
        }

        $isValidCode = $method->method_type === 'EMAIL_OTP'
            ? $this->emailOtpService->verify($request, $method, $request->string('code')->toString())
            : $this->totpService->verify(
                Crypt::decryptString($method->secret_encrypted),
                $request->string('code')->toString(),
            );

        if (! $isValidCode) {
            $this->auditService->record($request, 'MFA_FAILED', false, $user, 'invalid_totp_code', $method);

            throw ValidationException::withMessages([
                'code' => $method->method_type === 'EMAIL_OTP'
                    ? 'El codigo OTP no es valido o ya expiro.'
                    : 'El codigo de verificacion no es valido.',
            ]);
        }

        $method->forceFill([
            'last_used_at' => now(),
        ])->save();

        if ($method->method_type === 'EMAIL_OTP') {
            $this->emailOtpService->clear($request);
        }

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

        if ($this->mfaSessionService->challengeExpired($request)) {
            return $this->abortExpiredChallenge($request, $user);
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

        $this->emailOtpService->clear($request);
        $this->mfaSessionService->markVerified($request, $user, $method);
        $this->auditService->record($request, 'MFA_SUCCESS', true, $user, null, $method);

        return redirect()->to($this->mfaSessionService->intendedUrl($request, $this->redirector->for($user)));
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();
        $method = $this->resolveChallengeMethod($request);

        if ($user === null || $method === null) {
            return redirect()->route('login');
        }

        if ($this->mfaSessionService->challengeExpired($request)) {
            return $this->abortExpiredChallenge($request, $user);
        }

        if ($method->method_type !== 'EMAIL_OTP') {
            return redirect()->route('mfa.challenge.show');
        }

        if (! $this->emailOtpService->canResend($request)) {
            return redirect()
                ->route('mfa.challenge.show')
                ->with('status', 'Espera un momento antes de solicitar un nuevo codigo OTP.');
        }

        $this->emailOtpService->issueChallenge($request, $user, $method, true);
        $this->auditService->record($request, 'MFA_OTP_RESENT', true, $user, null, $method);

        return redirect()
            ->route('mfa.challenge.show')
            ->with('status', "Se envio un nuevo codigo OTP a {$method->destination_masked}.");
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

    protected function abortExpiredChallenge(Request $request, $user): RedirectResponse
    {
        $this->auditService->record($request, 'MFA_FAILED', false, $user, 'challenge_expired');
        $this->emailOtpService->clear($request);
        $this->mfaSessionService->clear($request);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('login')->with('status', 'La verificacion MFA expiro. Inicia sesion de nuevo.');
    }
}
