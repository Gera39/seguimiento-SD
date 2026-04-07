<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DidacticPlanModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'didactic_plan_unit_id',
        'module_number',
        'title',
        'topic_description',
        'theoretical_hours',
        'practical_hours',
        'learning_activity',
        'teaching_activity',
        'resources',
        'assessment_activity',
        'delivery_mode',
        'scheduled_date',
    ];

    protected function casts(): array
    {
        return [
            'module_number' => 'integer',
            'theoretical_hours' => 'decimal:2',
            'practical_hours' => 'decimal:2',
            'scheduled_date' => 'date',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(DidacticPlanUnit::class, 'didactic_plan_unit_id');
    }
}
