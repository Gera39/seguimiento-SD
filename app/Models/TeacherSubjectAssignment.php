<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherSubjectAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_subject_offering_id',
        'teacher_user_id',
        'assigned_by_user_id',
        'assignment_role_code',
        'assignment_status_code',
        'assigned_at',
        'released_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'released_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function offering(): BelongsTo
    {
        return $this->belongsTo(GroupSubjectOffering::class, 'group_subject_offering_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function didacticPlans(): HasMany
    {
        return $this->hasMany(DidacticPlan::class);
    }
}
