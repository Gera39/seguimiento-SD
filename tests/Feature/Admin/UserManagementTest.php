<?php

namespace Tests\Feature\Admin;

use App\Domain\Security\Enums\RoleCode;
use App\Models\Career;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Database\Seeders\Security\SecurityCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_management_page_with_real_users(): void
    {
        $this->seed(SecurityCatalogSeeder::class);

        $admin = User::factory()->create([
            'name' => 'Admin Principal',
            'email' => 'admin@example.com',
        ]);
        $teacher = User::factory()->create([
            'name' => 'Docente Demo',
            'email' => 'docente@example.com',
        ]);

        $this->assignRole($admin, RoleCode::ADMIN);
        $this->assignRole($teacher, RoleCode::DOCENTE);

        $this->actingAs($admin)
            ->get(route('demo.docentes', absolute: false))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('DocentesView')
                ->has('users', 2)
                ->where('users', fn ($users) => collect($users)->pluck('email')->sort()->values()->all() === [
                    'admin@example.com',
                    'docente@example.com',
                ])
                ->has('roleOptions', 4)
            );
    }

    public function test_admin_can_create_user_and_assign_multiple_roles(): void
    {
        $this->seed(SecurityCatalogSeeder::class);

        $admin = User::factory()->create();
        $career = Career::query()->create([
            'code' => 'TIC',
            'name' => 'Tecnologias de la Informacion',
            'short_name' => 'TIC',
            'educational_level' => 'TSU',
            'duration_terms' => 6,
            'is_active' => true,
        ]);

        $this->assignRole($admin, RoleCode::ADMIN);

        $response = $this->actingAs($admin)->post(route('demo.docentes.store', absolute: false), [
            'employee_number' => 'EMP-100',
            'name' => 'Maria Revision',
            'email' => 'maria.revision@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'roles' => [
                RoleCode::DOCENTE->value,
                RoleCode::REVISOR->value,
            ],
            'reviewer_career_ids' => [$career->id],
            'must_change_password' => true,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('demo.docentes', absolute: false));

        $user = User::query()->where('email', 'maria.revision@example.com')->firstOrFail();

        $this->assertSame('EMP-100', $user->employee_number);
        $this->assertTrue($user->must_change_password);
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasRole(RoleCode::DOCENTE));
        $this->assertTrue($user->hasRole(RoleCode::REVISOR));
        $this->assertDatabaseHas('user_role_assignments', [
            'user_id' => $user->id,
            'role_id' => Role::query()->where('code', RoleCode::REVISOR->value)->value('id'),
            'career_id' => $career->id,
            'is_active' => true,
        ]);
    }

    public function test_directivo_can_not_assign_admin_role(): void
    {
        $this->seed(SecurityCatalogSeeder::class);

        $director = User::factory()->create();
        $this->assignRole($director, RoleCode::DIRECTIVO);

        $this->actingAs($director)
            ->post(route('demo.docentes.store', absolute: false), [
                'employee_number' => 'EMP-200',
                'name' => 'Usuario Restringido',
                'email' => 'restringido@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'roles' => [RoleCode::ADMIN->value],
                'must_change_password' => false,
                'is_active' => true,
            ])
            ->assertSessionHasErrors('roles.0');

        $this->assertDatabaseMissing('users', [
            'email' => 'restringido@example.com',
        ]);
    }

    public function test_admin_can_update_existing_user_roles(): void
    {
        $this->seed(SecurityCatalogSeeder::class);

        $admin = User::factory()->create();
        $career = Career::query()->create([
            'code' => 'MEC',
            'name' => 'Mecatronica',
            'short_name' => 'MEC',
            'educational_level' => 'TSU',
            'duration_terms' => 6,
            'is_active' => true,
        ]);
        $managedUser = User::factory()->create([
            'email' => 'cambio.roles@example.com',
        ]);

        $this->assignRole($admin, RoleCode::ADMIN);
        $this->assignRole($managedUser, RoleCode::DOCENTE);

        $this->actingAs($admin)
            ->patch(route('demo.docentes.roles.update', $managedUser, absolute: false), [
                'roles' => [RoleCode::REVISOR->value],
                'reviewer_career_ids' => [$career->id],
            ])
            ->assertRedirect(route('demo.docentes', absolute: false));

        $managedUser->refresh();

        $this->assertFalse($managedUser->hasRole(RoleCode::DOCENTE));
        $this->assertTrue($managedUser->hasRole(RoleCode::REVISOR));
        $this->assertDatabaseHas('user_role_assignments', [
            'user_id' => $managedUser->id,
            'role_id' => Role::query()->where('code', RoleCode::DOCENTE->value)->value('id'),
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('user_role_assignments', [
            'user_id' => $managedUser->id,
            'role_id' => Role::query()->where('code', RoleCode::REVISOR->value)->value('id'),
            'career_id' => $career->id,
            'is_active' => true,
        ]);
    }

    protected function assignRole(User $user, RoleCode $roleCode): void
    {
        $roleId = Role::query()->where('code', $roleCode->value)->value('id');

        UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'is_active' => true,
        ]);
    }
}
