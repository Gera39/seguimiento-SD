<?php

namespace Tests\Feature\Security;
use App\Domain\Security\Enums\RoleCode;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Database\Seeders\Planning\PlanningCatalogSeeder;
use Database\Seeders\Security\SecurityCatalogSeeder;
use Database\Seeders\Security\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_seeder_creates_a_user_with_all_operational_roles(): void
    {
        $this->seed(SecurityCatalogSeeder::class);
        $this->seed(PlanningCatalogSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $user = User::query()->where('email', 'admin@seguimiento-sd.test')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(RoleCode::ADMIN));
        $this->assertTrue($user->hasRole(RoleCode::DIRECTIVO));
        $this->assertTrue($user->hasRole(RoleCode::REVISOR));
        $this->assertTrue($user->hasRole(RoleCode::DOCENTE));

        $this->assertDatabaseHas('teacher_subject_assignments', [
            'teacher_user_id' => $user->id,
            'is_active' => true,
        ]);

        $this->assertTrue(
            TeacherSubjectAssignment::query()
                ->where('teacher_user_id', $user->id)
                ->exists()
        );
    }
}
