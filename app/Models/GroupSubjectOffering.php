<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupSubjectOffering extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'career_subject_id',
        'modality_code',
        'scheduled_start_date',
        'scheduled_end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_start_date' => 'date',
            'scheduled_end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AcademicGroup::class, 'group_id');
    }

    public function careerSubject(): BelongsTo
    {
        return $this->belongsTo(CareerSubject::class);
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectAssignment::class);
    }
}
