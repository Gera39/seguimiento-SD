<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Career extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'short_name',
        'educational_level',
        'duration_terms',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'duration_terms' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function groups(): HasMany
    {
        return $this->hasMany(AcademicGroup::class);
    }

    public function curriculum(): HasMany
    {
        return $this->hasMany(CareerSubject::class);
    }
}
