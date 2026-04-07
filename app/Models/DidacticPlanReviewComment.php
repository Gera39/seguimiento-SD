<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DidacticPlanReviewComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'entity_type',
        'entity_id',
        'severity_code',
        'comment_text',
        'is_resolved',
        'resolved_by_user_id',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(DidacticPlanReview::class, 'review_id');
    }
}
