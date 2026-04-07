<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'subject_type',
        'default_total_hours',
        'default_theoretical_hours',
        'default_practical_hours',
        'credits',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_total_hours' => 'decimal:2',
            'default_theoretical_hours' => 'decimal:2',
            'default_practical_hours' => 'decimal:2',
            'credits' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function curriculumAssignments(): HasMany
    {
        return $this->hasMany(CareerSubject::class);
    }
}
