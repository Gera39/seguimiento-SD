<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DidacticPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_uuid',
        'plan_folio',
        'teacher_subject_assignment_id',
        'status_id',
        'version_number',
        'current_review_round',
        'general_objective',
        'course_intent',
        'methodology_notes',
        'general_observations',
        'submission_notes',
        'submitted_at',
        'locked_at',
        'feedback_released_at',
        'authorized_at',
        'authorized_by_user_id',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $attributes = [
        'version_number' => 1,
        'current_review_round' => 0,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $plan): void {
            if (blank($plan->public_uuid)) {
                $plan->public_uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'locked_at' => 'datetime',
            'feedback_released_at' => 'datetime',
            'authorized_at' => 'datetime',
            'version_number' => 'integer',
            'current_review_round' => 'integer',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TeacherSubjectAssignment::class, 'teacher_subject_assignment_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(PlanningStatus::class, 'status_id');
    }

    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(DidacticPlanUnit::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(DidacticPlanReference::class);
    }

    public function evaluationCriteria(): HasMany
    {
        return $this->hasMany(DidacticPlanEvaluationCriterion::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(DidacticPlanReview::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(DidacticPlanStatusHistory::class);
    }

    public function validationSnapshots(): HasMany
    {
        return $this->hasMany(DidacticPlanValidationSnapshot::class);
    }

    public function isTeacherEditable(): bool
    {
        return (bool) ($this->status?->is_teacher_editable ?? false);
    }
}
