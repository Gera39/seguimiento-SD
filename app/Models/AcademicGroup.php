<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicGroup extends Model
{
    use HasFactory;

    protected $table = 'groups';

    protected $fillable = [
        'career_id',
        'academic_period_id',
        'group_code',
        'shift_code',
        'term_number',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'term_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function offerings(): HasMany
    {
        return $this->hasMany(GroupSubjectOffering::class, 'group_id');
    }
}
