<?php

namespace App\Http\Controllers;

use App\Domain\Security\Mfa\UserMfaManager;
use App\Http\Requests\Profile\ConfirmMfaSetupRequest;
use App\Http\Requests\Profile\DisableMfaRequest;
use App\Http\Requests\Profile\RegenerateRecoveryCodesRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class ProfileMfaController extends Controller
{
    public function __construct(
        protected UserMfaManager $mfaManager,
    ) {
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return Redirect::route('login');
        }

        $activeMethod = $this->mfaManager->primaryMethodFor($user);

        if ($activeMethod !== null && $activeMethod->method_type === 'TOTP') {
            return Redirect::route('profile.edit')->with('status', 'La autenticacion multifactor ya esta activa.');
        }

        $this->mfaManager->createPendingTotp($user);

        return Redirect::route('profile.edit')->with('status', 'Escanea o registra la clave secreta y confirma un codigo para activar MFA con autenticador.');
    }

    public function storeEmailOtp(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return Redirect::route('login');
        }

        [$method, $recoveryCodes] = $this->mfaManager->enableEmailOtp($user);

        return Redirect::route('profile.edit')
            ->with('status', "OTP por correo activado. Los codigos llegaran a {$method->destination_masked}.")
            ->with('mfa_recovery_codes', $recoveryCodes);
    }

    public function confirm(ConfirmMfaSetupRequest $request): RedirectResponse
    {
        [$method, $recoveryCodes] = $this->mfaManager->confirmPendingTotp(
            $request->user(),
            $request->string('code')->toString(),
        );

        if ($method === null) {
            throw ValidationException::withMessages([
                'code' => 'No fue posible confirmar el codigo. Verifica la clave en tu autenticador.',
            ]);
        }

        return Redirect::route('profile.edit')
            ->with('status', 'MFA se activo correctamente. Guarda tus codigos de recuperacion.')
            ->with('mfa_recovery_codes', $recoveryCodes);
    }

    public function destroy(DisableMfaRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $this->mfaManager->disable($user);
        }

        return Redirect::route('profile.edit')->with('status', 'MFA se desactivo para esta cuenta.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $pendingMethod = $this->mfaManager->pendingMethodFor($request->user());

        if ($pendingMethod !== null) {
            $pendingMethod->delete();
        }

        return Redirect::route('profile.edit')->with('status', 'Se cancelo la configuracion pendiente de MFA.');
    }

    public function regenerateRecoveryCodes(RegenerateRecoveryCodesRequest $request): RedirectResponse
    {
        $recoveryCodes = $this->mfaManager->regenerateRecoveryCodes($request->user());

        if ($recoveryCodes === []) {
            return Redirect::route('profile.edit')->with('status', 'No hay un metodo MFA activo para regenerar codigos.');
        }

        return Redirect::route('profile.edit')
            ->with('status', 'Se regeneraron los codigos de recuperacion.')
            ->with('mfa_recovery_codes', $recoveryCodes);
    }
}
