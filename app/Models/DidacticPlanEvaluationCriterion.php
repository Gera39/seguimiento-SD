<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DidacticPlanEvaluationCriterion extends Model
{
    use HasFactory;

    protected $table = 'didactic_plan_evaluation_criteria';

    protected $fillable = [
        'didactic_plan_id',
        'didactic_plan_unit_id',
        'criterion_type_id',
        'criterion_name',
        'description',
        'evidence_description',
        'instrument_description',
        'weight_percentage',
        'minimum_score',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'weight_percentage' => 'decimal:2',
            'minimum_score' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function didacticPlan(): BelongsTo
    {
        return $this->belongsTo(DidacticPlan::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(DidacticPlanUnit::class, 'didactic_plan_unit_id');
    }

    public function criterionType(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriterionType::class, 'criterion_type_id');
    }
}
