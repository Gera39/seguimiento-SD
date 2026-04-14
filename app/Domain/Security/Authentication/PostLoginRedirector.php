<?php

namespace App\Domain\Security\Authentication;

use App\Domain\Security\Enums\RoleCode;
use App\Models\User;

class PostLoginRedirector
{
    public function for(User $user): string
    {
        if ($user->hasRole(RoleCode::ADMIN)) {
            return route('demo.docentes', absolute: false);
        }

        if ($user->hasRole(RoleCode::DIRECTIVO)) {
            return route('panel.director', absolute: false);
        }

        if ($user->hasRole(RoleCode::REVISOR)) {
            return route('demo.validaciones', absolute: false);
        }

        if ($user->hasRole(RoleCode::DOCENTE)) {
            return route('demo.secuencias', absolute: false);
        }

        return route('profile.edit', absolute: false);
    }
}
