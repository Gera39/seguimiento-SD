<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Admin\UserManagementService;
use App\Domain\Security\Enums\RoleCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManagedUserRequest;
use App\Http\Requests\Admin\UpdateManagedUserRolesRequest;
use App\Http\Requests\Admin\UpdateManagedUserStatusRequest;
use App\Models\Career;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function __construct(
        protected UserManagementService $userManagementService,
    ) {
    }

    public function index(Request $request): Response
    {
        $availableRoles = $this->userManagementService->availableRolesFor($request->user());

        $users = User::query()
            ->with([
                'activeRoleAssignments.role:id,code,name',
                'activeRoleAssignments.career:id,name,short_name',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (User $user): array {
                $roles = $user->activeRoleAssignments
                    ->map(fn (UserRoleAssignment $assignment) => [
                        'code' => $assignment->role?->code,
                        'name' => $assignment->role?->name,
                    ])
                    ->filter(fn (array $role) => filled($role['code']))
                    ->unique('code')
                    ->values();

                $reviewerCareers = $user->activeRoleAssignments
                    ->filter(fn (UserRoleAssignment $assignment) => $assignment->role?->code === RoleCode::REVISOR->value)
                    ->map(fn (UserRoleAssignment $assignment) => [
                        'id' => $assignment->career?->id,
                        'name' => $assignment->career?->short_name ?: $assignment->career?->name ?: 'Todas las carreras',
                    ])
                    ->unique('id')
                    ->values();

                return [
                    'id' => $user->id,
                    'employee_number' => $user->employee_number,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'roles' => $roles->all(),
                    'roles_label' => $roles->pluck('name')->implode(', '),
                    'reviewer_career_ids' => $reviewerCareers->pluck('id')->filter()->values()->all(),
                    'reviewer_careers_label' => $reviewerCareers->pluck('name')->implode(', '),
                    'reviewer_scope_label' => $roles->contains('code', RoleCode::REVISOR->value)
                        ? ($reviewerCareers->isEmpty() || $reviewerCareers->contains('id', null)
                            ? 'Todas las carreras'
                            : $reviewerCareers->pluck('name')->implode(', '))
                        : null,
                    'created_at' => optional($user->created_at)->format('d/m/Y'),
                    'update_status_url' => route('demo.docentes.status.update', $user, absolute: false),
                    'update_roles_url' => route('demo.docentes.roles.update', $user, absolute: false),
                ];
            })
            ->all();

        return Inertia::render('DocentesView', [
            'metrics' => [
                [
                    'label' => 'Usuarios activos',
                    'value' => User::query()->where('is_active', true)->count(),
                ],
                [
                    'label' => 'Docentes activos',
                    'value' => UserRoleAssignment::query()
                        ->where('is_active', true)
                        ->whereHas('role', fn ($query) => $query->where('code', RoleCode::DOCENTE->value))
                        ->distinct('user_id')
                        ->count('user_id'),
                ],
                [
                    'label' => 'Revisores activos',
                    'value' => UserRoleAssignment::query()
                        ->where('is_active', true)
                        ->whereHas('role', fn ($query) => $query->where('code', RoleCode::REVISOR->value))
                        ->distinct('user_id')
                        ->count('user_id'),
                ],
            ],
            'users' => $users,
            'roleOptions' => $availableRoles->map(fn (Role $role) => [
                'code' => $role->code,
                'name' => $role->name,
                'description' => $role->description,
            ])->values()->all(),
            'careerOptions' => Career::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn (Career $career) => [
                    'id' => $career->id,
                    'name' => $career->name,
                    'short_name' => $career->short_name,
                ])
                ->all(),
            'status' => session('status'),
        ]);
    }

    public function store(StoreManagedUserRequest $request): RedirectResponse
    {
        $availableRoleCodes = $this->userManagementService->availableRolesFor($request->user())->pluck('code')->all();
        $validated = $request->validated();
        $selectedRoleCodes = collect($validated['roles'])
            ->intersect($availableRoleCodes)
            ->values();
        $reviewerCareerIds = collect($validated['reviewer_career_ids'] ?? [])->values();

        DB::transaction(function () use ($request, $selectedRoleCodes, $reviewerCareerIds): void {
            $user = User::query()->create([
                'employee_number' => $request->validated('employee_number'),
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
                'must_change_password' => $request->boolean('must_change_password'),
                'is_active' => $request->boolean('is_active', true),
                'email_verified_at' => now(),
            ]);

            $this->userManagementService->syncRoleAssignments($user, $selectedRoleCodes->all(), $reviewerCareerIds->all(), $request->user()?->id);
        });

        return redirect()
            ->route('demo.docentes')
            ->with('status', 'El usuario se guardo y sus roles quedaron asignados correctamente.');
    }

    public function updateRoles(UpdateManagedUserRolesRequest $request, User $user): RedirectResponse
    {
        $availableRoles = $this->userManagementService->availableRolesFor($request->user());
        $availableRoleCodes = $availableRoles->pluck('code')->all();
        $validated = $request->validated();
        $selectedRoleCodes = collect($validated['roles'])
            ->intersect($availableRoleCodes)
            ->values();
        $reviewerCareerIds = collect($validated['reviewer_career_ids'] ?? [])->values();

        DB::transaction(function () use ($request, $user, $selectedRoleCodes, $reviewerCareerIds, $availableRoles): void {
            $this->userManagementService->deactivateAssignableRoles($user, $availableRoles);
            $this->userManagementService->syncRoleAssignments($user, $selectedRoleCodes->all(), $reviewerCareerIds->all(), $request->user()?->id);
        });

        return redirect()
            ->route('demo.docentes')
            ->with('status', 'Los roles del usuario se actualizaron correctamente.');
    }

    public function updateStatus(UpdateManagedUserStatusRequest $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user) && ! $request->boolean('is_active')) {
            return redirect()
                ->route('demo.docentes')
                ->with('status', 'No puedes desactivar tu propia cuenta desde esta pantalla.');
        }

        $this->userManagementService->updateActiveState($user, $request->boolean('is_active'));

        return redirect()
            ->route('demo.docentes')
            ->with('status', $request->boolean('is_active')
                ? 'La cuenta se reactivo correctamente.'
                : 'La cuenta se desactivo correctamente.');
    }
}
