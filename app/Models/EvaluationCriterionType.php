<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCriterionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function criteria(): HasMany
    {
        return $this->hasMany(DidacticPlanEvaluationCriterion::class, 'criterion_type_id');
    }
}
