<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DidacticPlanReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'didactic_plan_id',
        'reference_type',
        'citation',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function didacticPlan(): BelongsTo
    {
        return $this->belongsTo(DidacticPlan::class);
    }
}
