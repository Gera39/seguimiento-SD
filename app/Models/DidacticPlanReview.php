<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DidacticPlanReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'didactic_plan_id',
        'review_round',
        'review_stage_code',
        'reviewer_user_id',
        'assigned_by_user_id',
        'decision_status_id',
        'general_comments',
        'started_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'review_round' => 'integer',
            'started_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function didacticPlan(): BelongsTo
    {
        return $this->belongsTo(DidacticPlan::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DidacticPlanReviewComment::class, 'review_id');
    }
}
