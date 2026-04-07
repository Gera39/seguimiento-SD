<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'career_id',
        'subject_id',
        'term_number',
        'curricular_block',
        'total_hours',
        'theoretical_hours',
        'practical_hours',
        'credits',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'term_number' => 'integer',
            'total_hours' => 'decimal:2',
            'theoretical_hours' => 'decimal:2',
            'practical_hours' => 'decimal:2',
            'credits' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function offerings(): HasMany
    {
        return $this->hasMany(GroupSubjectOffering::class);
    }
}
