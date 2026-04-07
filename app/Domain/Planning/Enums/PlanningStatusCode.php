<?php

namespace App\Domain\Planning\Enums;

enum PlanningStatusCode: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case UNDER_REVIEW = 'UNDER_REVIEW';
    case FEEDBACK = 'FEEDBACK';
    case AUTHORIZED = 'AUTHORIZED';
    case REJECTED = 'REJECTED';
}
