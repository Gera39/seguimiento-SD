<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DidacticPlanStatusHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'didactic_plan_status_history';

    protected $fillable = [
        'didactic_plan_id',
        'from_status_id',
        'to_status_id',
        'transition_rule_id',
        'changed_by_user_id',
        'comments',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    public function didacticPlan(): BelongsTo
    {
        return $this->belongsTo(DidacticPlan::class);
    }

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(PlanningStatus::class, 'from_status_id');
    }

    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(PlanningStatus::class, 'to_status_id');
    }

    public function transitionRule(): BelongsTo
    {
        return $this->belongsTo(PlanningTransitionRule::class, 'transition_rule_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
