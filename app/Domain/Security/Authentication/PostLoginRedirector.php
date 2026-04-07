<?php

namespace App\Domain\Security\Authentication;

use App\Domain\Security\Enums\RoleCode;
use App\Models\User;

class PostLoginRedirector
{
    public function for(User $user): string
    {
        if ($user->hasRole(RoleCode::ADMIN)) {
            return route('dashboard', absolute: false);
        }

        if ($user->hasRole(RoleCode::DIRECTIVO)) {
            return route('panel.director', absolute: false);
        }

        if ($user->hasRole(RoleCode::REVISOR)) {
            return route('panel.revisor', absolute: false);
        }

        if ($user->hasRole(RoleCode::DOCENTE)) {
            return route('panel.docente', absolute: false);
        }

        return route('dashboard', absolute: false);
    }
}
