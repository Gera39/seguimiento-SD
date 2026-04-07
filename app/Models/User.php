<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Domain\Security\Enums\PermissionCode;
use App\Domain\Security\Enums\RoleCode;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_number',
        'name',
        'email',
        'password',
        'must_change_password',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function roleAssignments(): HasMany
    {
        return $this->hasMany(UserRoleAssignment::class);
    }

    public function activeRoleAssignments(): HasMany
    {
        return $this->roleAssignments()->where('is_active', true);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role_assignments')
            ->withPivot(['id', 'career_id', 'valid_from', 'valid_to', 'is_active', 'assigned_by_user_id'])
            ->withTimestamps();
    }

    public function mfaMethods(): HasMany
    {
        return $this->hasMany(UserMfaMethod::class);
    }

    public function activeMfaMethods(): HasMany
    {
        return $this->mfaMethods()->where('is_active', true);
    }

    public function primaryMfaMethod(): HasOne
    {
        return $this->hasOne(UserMfaMethod::class)
            ->where('is_active', true)
            ->whereNotNull('confirmed_at')
            ->where('is_primary', true)
            ->latestOfMany();
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectAssignment::class, 'teacher_user_id');
    }

    public function hasConfirmedMfaMethod(): bool
    {
        return $this->activeMfaMethods()
            ->whereNotNull('confirmed_at')
            ->exists();
    }

    public function hasRole(RoleCode|string $role): bool
    {
        $roleCode = $role instanceof RoleCode ? $role->value : $role;

        return $this->activeRoleAssignments()
            ->whereHas('role', fn ($query) => $query->where('code', $roleCode))
            ->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        $roleCodes = collect($roles)
            ->map(fn (RoleCode|string $role) => $role instanceof RoleCode ? $role->value : $role)
            ->all();

        return $this->activeRoleAssignments()
            ->whereHas('role', fn ($query) => $query->whereIn('code', $roleCodes))
            ->exists();
    }

    public function hasPermission(PermissionCode|string $permission): bool
    {
        $permissionCode = $permission instanceof PermissionCode ? $permission->value : $permission;

        return $this->roles()
            ->wherePivot('is_active', true)
            ->whereHas('permissions', fn ($query) => $query->where('code', $permissionCode))
            ->exists();
    }
}
