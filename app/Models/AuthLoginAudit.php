<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthLoginAudit extends Model
{
    use HasFactory;

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'session_id',
        'mfa_method_id',
        'event_type',
        'is_success',
        'failure_reason',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'is_success' => 'boolean',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mfaMethod(): BelongsTo
    {
        return $this->belongsTo(UserMfaMethod::class, 'mfa_method_id');
    }
}
