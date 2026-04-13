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
        'field_path',
        'field_label',
        'severity_code',
        'comment_text',
        'observed_value_snapshot',
        'comment_status_code',
        'teacher_response',
        'teacher_responded_by_user_id',
        'teacher_responded_at',
        'updated_value_snapshot',
        'is_resolved',
        'resolved_by_user_id',
        'resolved_at',
        'validated_by_user_id',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
            'teacher_responded_at' => 'datetime',
            'resolved_at' => 'datetime',
            'validated_at' => 'datetime',
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(DidacticPlanReview::class, 'review_id');
    }

    public function teacherResponder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_responded_by_user_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_user_id');
    }
}
