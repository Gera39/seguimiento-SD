<?php

namespace App\Domain\Admin;

use App\Domain\Security\Enums\RoleCode;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Support\Collection;

class UserManagementService
{
    public function availableRolesFor(?User $user): Collection
    {
        $codes = $user?->hasRole(RoleCode::ADMIN)
            ? [
                RoleCode::ADMIN->value,
                RoleCode::DIRECTIVO->value,
                RoleCode::REVISOR->value,
                RoleCode::DOCENTE->value,
            ]
            : [
                RoleCode::REVISOR->value,
                RoleCode::DOCENTE->value,
            ];

        return Role::query()
            ->whereIn('code', $codes)
            ->orderBy('name')
            ->get();
    }

    public function syncRoleAssignments(User $user, array $selectedRoleCodes, array $reviewerCareerIds, ?int $assignedByUserId): void
    {
        $rolesByCode = Role::query()
            ->whereIn('code', $selectedRoleCodes)
            ->get()
            ->keyBy('code');

        foreach ($selectedRoleCodes as $roleCode) {
            $role = $rolesByCode->get($roleCode);

            if ($role === null) {
                continue;
            }

            if ($roleCode === RoleCode::REVISOR->value) {
                $scopeCareerIds = $reviewerCareerIds === [] ? [null] : array_values(array_unique($reviewerCareerIds));

                foreach ($scopeCareerIds as $careerId) {
                    UserRoleAssignment::query()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'role_id' => $role->id,
                            'career_id' => $careerId,
                        ],
                        [
                            'is_active' => true,
                            'assigned_by_user_id' => $assignedByUserId,
                        ],
                    );
                }

                continue;
            }

            UserRoleAssignment::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                    'career_id' => null,
                ],
                [
                    'is_active' => true,
                    'assigned_by_user_id' => $assignedByUserId,
                ],
            );
        }
    }

    public function deactivateAssignableRoles(User $user, Collection $availableRoles): void
    {
        $allowedRoleIds = $availableRoles->pluck('id');

        UserRoleAssignment::query()
            ->where('user_id', $user->id)
            ->whereIn('role_id', $allowedRoleIds)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }

    public function updateActiveState(User $user, bool $isActive): void
    {
        $user->forceFill([
            'is_active' => $isActive,
        ])->save();
    }
}
