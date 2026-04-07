<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Domain\Security\Enums\RoleCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
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

        $request->user()?->forceFill([
            'last_login_at' => now(),
        ])->save();

        return redirect()->intended($this->redirectPathFor($request));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function redirectPathFor(Request $request): string
    {
        $user = $request->user();

        if ($user?->hasRole(RoleCode::ADMIN)) {
            return route('dashboard', absolute: false);
        }

        if ($user?->hasRole(RoleCode::DIRECTIVO)) {
            return route('panel.director', absolute: false);
        }

        if ($user?->hasRole(RoleCode::REVISOR)) {
            return route('panel.revisor', absolute: false);
        }

        if ($user?->hasRole(RoleCode::DOCENTE)) {
            return route('panel.docente', absolute: false);
        }

        return route('dashboard', absolute: false);
    }
}
