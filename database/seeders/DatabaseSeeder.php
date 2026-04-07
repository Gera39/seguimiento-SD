<?php

namespace Database\Seeders;

use App\Domain\Security\Enums\RoleCode;
use App\Models\AcademicGroup;
use App\Models\AcademicPeriod;
use App\Models\Career;
use App\Models\CareerSubject;
use App\Models\GroupSubjectOffering;
use App\Models\User;
use App\Models\Role;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use App\Models\UserRoleAssignment;
use Database\Seeders\Planning\PlanningCatalogSeeder;
use Database\Seeders\Security\SecurityCatalogSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SecurityCatalogSeeder::class,
            PlanningCatalogSeeder::class,
        ]);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $docenteRoleId = Role::query()
            ->where('code', RoleCode::DOCENTE->value)
            ->value('id');

        if ($docenteRoleId !== null) {
            UserRoleAssignment::query()->firstOrCreate([
                'user_id' => $user->id,
                'role_id' => $docenteRoleId,
                'career_id' => null,
            ], [
                'is_active' => true,
            ]);
        }

        $career = Career::query()->firstOrCreate(
            ['code' => 'TIC-UTH'],
            [
                'name' => 'Tecnologias de la Informacion',
                'short_name' => 'TIC',
                'educational_level' => 'TSU',
                'duration_terms' => 6,
                'is_active' => true,
            ],
        );

        $subject = Subject::query()->firstOrCreate(
            ['code' => 'PD-101'],
            [
                'name' => 'Planeacion Didactica',
                'subject_type' => 'REGULAR',
                'default_total_hours' => 60,
                'default_theoretical_hours' => 30,
                'default_practical_hours' => 30,
                'credits' => 4,
                'is_active' => true,
            ],
        );

        $period = AcademicPeriod::query()->firstOrCreate(
            ['code' => '2026-1'],
            [
                'name' => 'Enero-Abril 2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-04-30',
                'status_code' => 'ACTIVE',
                'is_active' => true,
            ],
        );

        $group = AcademicGroup::query()->firstOrCreate(
            [
                'career_id' => $career->id,
                'academic_period_id' => $period->id,
                'group_code' => '1A',
            ],
            [
                'shift_code' => 'MORNING',
                'term_number' => 1,
                'is_active' => true,
            ],
        );

        $careerSubject = CareerSubject::query()->firstOrCreate(
            [
                'career_id' => $career->id,
                'subject_id' => $subject->id,
                'term_number' => 1,
            ],
            [
                'curricular_block' => 'Formacion Base',
                'total_hours' => 60,
                'theoretical_hours' => 30,
                'practical_hours' => 30,
                'credits' => 4,
                'is_active' => true,
            ],
        );

        $offering = GroupSubjectOffering::query()->firstOrCreate(
            [
                'group_id' => $group->id,
                'career_subject_id' => $careerSubject->id,
            ],
            [
                'modality_code' => 'PRESENTIAL',
                'is_active' => true,
            ],
        );

        TeacherSubjectAssignment::query()->firstOrCreate(
            [
                'group_subject_offering_id' => $offering->id,
                'teacher_user_id' => $user->id,
            ],
            [
                'assignment_role_code' => 'PRIMARY',
                'assignment_status_code' => 'ACTIVE',
                'assigned_at' => now(),
                'is_active' => true,
            ],
        );
    }
}
