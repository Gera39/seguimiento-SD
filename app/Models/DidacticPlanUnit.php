<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DidacticPlanUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'didactic_plan_id',
        'unit_number',
        'title',
        'learning_objective',
        'planned_hours',
        'progress_percentage',
        'start_week',
        'end_week',
        'teaching_strategy',
        'learning_evidence',
        'assessment_strategy',
    ];

    protected function casts(): array
    {
        return [
            'unit_number' => 'integer',
            'planned_hours' => 'decimal:2',
            'progress_percentage' => 'decimal:2',
            'start_week' => 'integer',
            'end_week' => 'integer',
        ];
    }

    public function didacticPlan(): BelongsTo
    {
        return $this->belongsTo(DidacticPlan::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(DidacticPlanModule::class);
    }
}
