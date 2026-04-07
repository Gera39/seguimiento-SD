<?php

namespace App\Domain\Security\Enums;

enum RoleCode: string
{
    case ADMIN = 'ADMIN';
    case DIRECTIVO = 'DIRECTIVO';
    case DOCENTE = 'DOCENTE';
    case REVISOR = 'REVISOR';
}
