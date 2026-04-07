<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMfaRecoveryCode extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'mfa_method_id',
        'code_hash',
        'used_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(UserMfaMethod::class, 'mfa_method_id');
    }
}
