<?php

namespace App\Http\Middleware;

use App\Domain\Security\Enums\RoleCode;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user === null ? null : [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => [
                        'isAdmin' => $user->hasRole(RoleCode::ADMIN),
                        'isDirectivo' => $user->hasRole(RoleCode::DIRECTIVO),
                        'isDocente' => $user->hasRole(RoleCode::DOCENTE),
                        'isRevisor' => $user->hasRole(RoleCode::REVISOR),
                    ],
                ],
            ],
        ];
    }
}
