<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanningStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_teacher_editable',
        'is_teacher_deletable',
        'is_terminal',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_teacher_editable' => 'boolean',
            'is_teacher_deletable' => 'boolean',
            'is_terminal' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function outgoingTransitions(): HasMany
    {
        return $this->hasMany(PlanningTransitionRule::class, 'from_status_id');
    }

    public function incomingTransitions(): HasMany
    {
        return $this->hasMany(PlanningTransitionRule::class, 'to_status_id');
    }

    public function didacticPlans(): HasMany
    {
        return $this->hasMany(DidacticPlan::class, 'status_id');
    }
}
