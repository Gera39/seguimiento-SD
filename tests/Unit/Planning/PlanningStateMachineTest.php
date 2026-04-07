<?php

namespace Tests\Unit\Planning;

use App\Domain\Planning\Enums\PlanningStatusCode;
use App\Domain\Planning\Exceptions\InvalidPlanningTransitionException;
use App\Domain\Planning\StateMachine\PlanningStateMachine;
use App\Domain\Security\Enums\RoleCode;
use App\Models\AcademicGroup;
use App\Models\AcademicPeriod;
use App\Models\Career;
use App\Models\CareerSubject;
use App\Models\DidacticPlan;
use App\Models\GroupSubjectOffering;
use App\Models\PlanningStatus;
use App\Models\Role;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Database\Seeders\Planning\PlanningCatalogSeeder;
use Database\Seeders\Security\SecurityCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanningStateMachineTest extends TestCase
{
    use RefreshDatabase;

    public function test_docente_can_submit_a_draft_plan(): void
    {
        $this->seedCatalogs();

        $teacher = User::factory()->create();
        $this->assignRole($teacher, RoleCode::DOCENTE);

        $plan = $this->createPlanFor($teacher, PlanningStatusCode::DRAFT);

        $stateMachine = app(PlanningStateMachine::class);
        $updatedPlan = $stateMachine->transition($teacher, $plan, PlanningStatusCode::SUBMITTED);

        $this->assertSame(PlanningStatusCode::SUBMITTED->value, $updatedPlan->status->code);
        $this->assertNotNull($updatedPlan->submitted_at);
        $this->assertNotNull($updatedPlan->locked_at);
        $this->assertSame(1, $updatedPlan->current_review_round);
        $this->assertDatabaseHas('didactic_plan_status_history', [
            'didactic_plan_id' => $plan->id,
            'to_status_id' => $updatedPlan->status_id,
            'changed_by_user_id' => $teacher->id,
        ]);
    }

    public function test_feedback_transition_requires_comment_for_revisor(): void
    {
        $this->seedCatalogs();

        $reviewer = User::factory()->create();
        $this->assignRole($reviewer, RoleCode::REVISOR);

        $plan = $this->createPlanFor($reviewer, PlanningStatusCode::UNDER_REVIEW);

        $this->expectException(InvalidPlanningTransitionException::class);

        app(PlanningStateMachine::class)->transition($reviewer, $plan, PlanningStatusCode::FEEDBACK);
    }

    protected function seedCatalogs(): void
    {
        $this->seed(SecurityCatalogSeeder::class);
        $this->seed(PlanningCatalogSeeder::class);
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

    protected function createPlanFor(User $user, PlanningStatusCode $statusCode): DidacticPlan
    {
        $career = Career::query()->create([
            'code' => 'TIC',
            'name' => 'Tecnologias de la Informacion',
            'short_name' => 'TIC',
            'educational_level' => 'TSU',
            'duration_terms' => 6,
            'is_active' => true,
        ]);

        $subject = Subject::query()->create([
            'code' => 'ASI-101',
            'name' => 'Aplicaciones Web',
            'subject_type' => 'REGULAR',
            'default_total_hours' => 60,
            'default_theoretical_hours' => 30,
            'default_practical_hours' => 30,
            'credits' => 4,
            'is_active' => true,
        ]);

        $period = AcademicPeriod::query()->create([
            'code' => '2026-1',
            'name' => 'Enero-Abril 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-04-30',
            'status_code' => 'ACTIVE',
            'is_active' => true,
        ]);

        $group = AcademicGroup::query()->create([
            'career_id' => $career->id,
            'academic_period_id' => $period->id,
            'group_code' => 'A',
            'shift_code' => 'MORNING',
            'term_number' => 1,
            'is_active' => true,
        ]);

        $careerSubject = CareerSubject::query()->create([
            'career_id' => $career->id,
            'subject_id' => $subject->id,
            'term_number' => 1,
            'curricular_block' => 'Formacion Base',
            'total_hours' => 60,
            'theoretical_hours' => 30,
            'practical_hours' => 30,
            'credits' => 4,
            'is_active' => true,
        ]);

        $offering = GroupSubjectOffering::query()->create([
            'group_id' => $group->id,
            'career_subject_id' => $careerSubject->id,
            'modality_code' => 'PRESENTIAL',
            'is_active' => true,
        ]);

        $assignment = TeacherSubjectAssignment::query()->create([
            'group_subject_offering_id' => $offering->id,
            'teacher_user_id' => $user->id,
            'assignment_role_code' => 'PRIMARY',
            'assignment_status_code' => 'ACTIVE',
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        return DidacticPlan::query()->create([
            'plan_folio' => 'PLAN-001-'.$statusCode->value,
            'teacher_subject_assignment_id' => $assignment->id,
            'status_id' => PlanningStatus::query()->where('code', $statusCode->value)->value('id'),
            'general_objective' => 'Objetivo general de prueba',
            'created_by_user_id' => $user->id,
            'updated_by_user_id' => $user->id,
        ])->load('status');
    }
}
