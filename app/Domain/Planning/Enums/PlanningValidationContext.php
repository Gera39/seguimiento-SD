<?php

namespace App\Domain\Planning\Enums;

enum PlanningValidationContext: string
{
    case DRAFT_SAVE = 'DRAFT_SAVE';
    case SUBMISSION = 'SUBMISSION';
    case AUTHORIZATION = 'AUTHORIZATION';
}
