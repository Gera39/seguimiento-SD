<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DidacticPlanValidationSnapshot extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'didactic_plan_id',
        'validation_context',
        'total_units',
        'total_modules',
        'total_unit_hours',
        'total_module_hours',
        'total_progress_percentage',
        'total_evaluation_percentage',
        'is_valid',
        'validation_details',
        'created_by_user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'total_units' => 'integer',
            'total_modules' => 'integer',
            'total_unit_hours' => 'decimal:2',
            'total_module_hours' => 'decimal:2',
            'total_progress_percentage' => 'decimal:2',
            'total_evaluation_percentage' => 'decimal:2',
            'is_valid' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function didacticPlan(): BelongsTo
    {
        return $this->belongsTo(DidacticPlan::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
