<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserMfaMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'method_type',
        'label',
        'secret_encrypted',
        'destination_masked',
        'is_primary',
        'confirmed_at',
        'last_used_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'confirmed_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recoveryCodes(): HasMany
    {
        return $this->hasMany(UserMfaRecoveryCode::class, 'mfa_method_id');
    }
}
