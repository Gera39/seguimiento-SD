<?php

namespace Database\Seeders\Security;

use App\Domain\Security\Enums\RoleCode;
use App\Models\AcademicGroup;
use App\Models\AcademicPeriod;
use App\Models\Career;
use App\Models\CareerSubject;
use App\Models\GroupSubjectOffering;
use App\Models\Role;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'admin@seguimiento-sd.test')],
            [
                'employee_number' => env('SUPERADMIN_EMPLOYEE_NUMBER', 'ADMIN-001'),
                'name' => env('SUPERADMIN_NAME', 'Super Administrador'),
                'email_verified_at' => now(),
                'password' => Hash::make(env('SUPERADMIN_PASSWORD', 'Admin12345!')),
                'must_change_password' => false,
                'is_active' => true,
            ],
        );

        $roleIds = Role::query()
            ->whereIn('code', [
                RoleCode::ADMIN->value,
                RoleCode::DIRECTIVO->value,
                RoleCode::REVISOR->value,
                RoleCode::DOCENTE->value,
            ])
            ->pluck('id', 'code');

        foreach ($roleIds as $roleId) {
            UserRoleAssignment::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'career_id' => null,
                ],
                [
                    'is_active' => true,
                    'assigned_by_user_id' => $user->id,
                ],
            );
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

        TeacherSubjectAssignment::query()->updateOrCreate(
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
