<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningTransitionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_status_id',
        'to_status_id',
        'triggered_by_role_id',
        'transition_code',
        'requires_comment',
        'reopens_for_editing',
    ];

    protected function casts(): array
    {
        return [
            'requires_comment' => 'boolean',
            'reopens_for_editing' => 'boolean',
        ];
    }

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(PlanningStatus::class, 'from_status_id');
    }

    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(PlanningStatus::class, 'to_status_id');
    }

    public function triggeredByRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'triggered_by_role_id');
    }
}
