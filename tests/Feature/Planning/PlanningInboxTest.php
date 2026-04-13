<?php

namespace Tests\Feature\Planning;

use App\Domain\Planning\Enums\PlanningStatusCode;
use App\Domain\Security\Enums\RoleCode;
use App\Models\AcademicGroup;
use App\Models\AcademicPeriod;
use App\Models\Career;
use App\Models\CareerSubject;
use App\Models\DidacticPlan;
use App\Models\GroupSubjectOffering;
use App\Models\Role;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Database\Seeders\Planning\PlanningCatalogSeeder;
use Database\Seeders\Security\SecurityCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PlanningInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviewer_queue_can_filter_by_status_and_search_term(): void
    {
        $this->seedCatalogs();

        $reviewer = User::factory()->create([
            'name' => 'Revisor UTH',
            'email' => 'revisor@example.com',
        ]);
        $teacherPending = User::factory()->create([
            'name' => 'Ana Pendiente',
            'email' => 'ana.pendiente@example.com',
        ]);
        $teacherReview = User::factory()->create([
            'name' => 'Bruno Revision',
            'email' => 'bruno.revision@example.com',
        ]);

        $this->assignRole($reviewer, RoleCode::REVISOR);
        $this->assignRole($teacherPending, RoleCode::DOCENTE);
        $this->assignRole($teacherReview, RoleCode::DOCENTE);

        $pendingAssignment = $this->createAssignmentForTeacher($teacherPending);
        $reviewAssignment = $this->createAssignmentForTeacher($teacherReview);

        $pendingPlan = $this->createPlan($teacherPending, $pendingAssignment->id);
        $reviewPlan = $this->createPlan($teacherReview, $reviewAssignment->id);

        $this->actingAs($teacherPending)
            ->post(route('plans.submit', $pendingPlan, absolute: false))
            ->assertRedirect();

        $this->actingAs($teacherReview)
            ->post(route('plans.submit', $reviewPlan, absolute: false))
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->post(route('plans.start-review', $reviewPlan, absolute: false))
            ->assertRedirect();

        $response = $this->actingAs($reviewer)
            ->get('/validaciones?status=UNDER_REVIEW&search=Bruno');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('ValidacionSecuencias')
            ->where('filters.status', PlanningStatusCode::UNDER_REVIEW->value)
            ->where('filters.search', 'Bruno')
            ->where('summary.filtered', 1)
            ->has('plans', 1)
            ->where('plans.0.teacher', 'Bruno Revision')
            ->where('plans.0.statusCode', PlanningStatusCode::UNDER_REVIEW->value)
            ->where('plans.0.primaryAction.label', 'Continuar revision'));
    }

    public function test_director_dashboard_shows_final_validation_actions_for_relevant_statuses(): void
    {
        $this->seedCatalogs();

        $reviewer = User::factory()->create([
            'name' => 'Revisor Final',
            'email' => 'revisor.final@example.com',
        ]);
        $director = User::factory()->create([
            'name' => 'Directora UTH',
            'email' => 'directora@example.com',
        ]);
        $teacherUnderReview = User::factory()->create([
            'name' => 'Claudia Revision',
            'email' => 'claudia.revision@example.com',
        ]);
        $teacherAuthorized = User::factory()->create([
            'name' => 'Dario Autorizado',
            'email' => 'dario.autorizado@example.com',
        ]);

        $this->assignRole($reviewer, RoleCode::REVISOR);
        $this->assignRole($director, RoleCode::DIRECTIVO);
        $this->assignRole($teacherUnderReview, RoleCode::DOCENTE);
        $this->assignRole($teacherAuthorized, RoleCode::DOCENTE);

        $underReviewAssignment = $this->createAssignmentForTeacher($teacherUnderReview);
        $authorizedAssignment = $this->createAssignmentForTeacher($teacherAuthorized);

        $underReviewPlan = $this->createPlan($teacherUnderReview, $underReviewAssignment->id);
        $authorizedPlan = $this->createPlan($teacherAuthorized, $authorizedAssignment->id);

        $this->actingAs($teacherUnderReview)
            ->post(route('plans.submit', $underReviewPlan, absolute: false))
            ->assertRedirect();

        $this->actingAs($teacherAuthorized)
            ->post(route('plans.submit', $authorizedPlan, absolute: false))
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->post(route('plans.start-review', $underReviewPlan, absolute: false))
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->post(route('plans.start-review', $authorizedPlan, absolute: false))
            ->assertRedirect();

        $this->actingAs($director)
            ->post(route('plans.authorize', $authorizedPlan, absolute: false), [
                'general_comments' => 'Aprobada para su cierre institucional.',
            ])
            ->assertRedirect();

        $response = $this->actingAs($director)->get('/panel-director');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('PanelDirector')
            ->has('plans', 2)
            ->where('plans.0.teacher', 'Claudia Revision')
            ->where('plans.0.statusCode', PlanningStatusCode::UNDER_REVIEW->value)
            ->where('plans.0.primaryAction.label', 'Validacion final')
            ->where('plans.1.teacher', 'Dario Autorizado')
            ->where('plans.1.statusCode', PlanningStatusCode::AUTHORIZED->value)
            ->where('plans.1.primaryAction.label', 'Ver cierre'));
    }

    public function test_reviewer_only_sees_plans_from_assigned_careers(): void
    {
        $this->seedCatalogs();

        $careerVisible = Career::query()->create([
            'code' => 'TIC',
            'name' => 'Tecnologias de la Informacion',
            'short_name' => 'TIC',
            'educational_level' => 'TSU',
            'duration_terms' => 6,
            'is_active' => true,
        ]);
        $careerHidden = Career::query()->create([
            'code' => 'MEC',
            'name' => 'Mecatronica',
            'short_name' => 'MEC',
            'educational_level' => 'TSU',
            'duration_terms' => 6,
            'is_active' => true,
        ]);

        $reviewer = User::factory()->create([
            'name' => 'Revisor TIC',
            'email' => 'revisor.tic@example.com',
        ]);
        $teacherVisible = User::factory()->create();
        $teacherHidden = User::factory()->create();

        $this->assignRole($reviewer, RoleCode::REVISOR, $careerVisible->id);
        $this->assignRole($teacherVisible, RoleCode::DOCENTE);
        $this->assignRole($teacherHidden, RoleCode::DOCENTE);

        $visibleAssignment = $this->createAssignmentForTeacher($teacherVisible, $careerVisible);
        $hiddenAssignment = $this->createAssignmentForTeacher($teacherHidden, $careerHidden);

        $visiblePlan = $this->createPlan($teacherVisible, $visibleAssignment->id);
        $hiddenPlan = $this->createPlan($teacherHidden, $hiddenAssignment->id);

        $this->actingAs($teacherVisible)
            ->post(route('plans.submit', $visiblePlan, absolute: false))
            ->assertRedirect();

        $this->actingAs($teacherHidden)
            ->post(route('plans.submit', $hiddenPlan, absolute: false))
            ->assertRedirect();

        $response = $this->actingAs($reviewer)->get(route('demo.validaciones', absolute: false));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('ValidacionSecuencias')
            ->where('summary.totalVisible', 1)
            ->has('plans', 1)
            ->where('plans.0.career', 'Tecnologias de la Informacion'));
    }

    public function test_reviewer_cannot_access_review_screen_for_plan_outside_scope(): void
    {
        $this->seedCatalogs();

        $careerVisible = Career::query()->create([
            'code' => 'TIC-ACCESS',
            'name' => 'Tecnologias Acceso',
            'short_name' => 'TIC',
            'educational_level' => 'TSU',
            'duration_terms' => 6,
            'is_active' => true,
        ]);
        $careerHidden = Career::query()->create([
            'code' => 'MEC-ACCESS',
            'name' => 'Mecatronica Acceso',
            'short_name' => 'MEC',
            'educational_level' => 'TSU',
            'duration_terms' => 6,
            'is_active' => true,
        ]);

        $reviewer = User::factory()->create();
        $teacher = User::factory()->create();

        $this->assignRole($reviewer, RoleCode::REVISOR, $careerVisible->id);
        $this->assignRole($teacher, RoleCode::DOCENTE);

        $assignment = $this->createAssignmentForTeacher($teacher, $careerHidden);
        $plan = $this->createPlan($teacher, $assignment->id);

        $this->actingAs($teacher)
            ->post(route('plans.submit', $plan, absolute: false))
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->get(route('plans.review.show', $plan, absolute: false))
            ->assertForbidden();
    }

    protected function seedCatalogs(): void
    {
        $this->seed(SecurityCatalogSeeder::class);
        $this->seed(PlanningCatalogSeeder::class);
    }

    protected function assignRole(User $user, RoleCode $roleCode, ?int $careerId = null): void
    {
        $roleId = Role::query()->where('code', $roleCode->value)->value('id');

        UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'career_id' => $careerId,
            'is_active' => true,
        ]);
    }

    protected function createAssignmentForTeacher(User $teacher, ?Career $career = null): TeacherSubjectAssignment
    {
        $career ??= Career::query()->create([
            'code' => 'TIC-'.$teacher->id,
            'name' => 'Tecnologias '.$teacher->id,
            'short_name' => 'TIC',
            'educational_level' => 'TSU',
            'duration_terms' => 6,
            'is_active' => true,
        ]);

        $subject = Subject::query()->create([
            'code' => 'ASI-'.$teacher->id,
            'name' => 'Aplicaciones Web '.$teacher->id,
            'subject_type' => 'REGULAR',
            'default_total_hours' => 60,
            'default_theoretical_hours' => 30,
            'default_practical_hours' => 30,
            'credits' => 4,
            'is_active' => true,
        ]);

        $period = AcademicPeriod::query()->create([
            'code' => '2026-'.$teacher->id,
            'name' => 'Periodo '.$teacher->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-04-30',
            'status_code' => 'ACTIVE',
            'is_active' => true,
        ]);

        $group = AcademicGroup::query()->create([
            'career_id' => $career->id,
            'academic_period_id' => $period->id,
            'group_code' => 'A'.$teacher->id,
            'shift_code' => 'MORNING',
            'term_number' => 1,
            'is_active' => true,
        ]);

        $careerSubject = CareerSubject::query()->create([
            'career_id' => $career->id,
            'subject_id' => $subject->id,
            'term_number' => 1,
            'curricular_block' => 'Base',
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

        return TeacherSubjectAssignment::query()->create([
            'group_subject_offering_id' => $offering->id,
            'teacher_user_id' => $teacher->id,
            'assignment_role_code' => 'PRIMARY',
            'assignment_status_code' => 'ACTIVE',
            'assigned_at' => now(),
            'is_active' => true,
        ]);
    }

    protected function createPlan(User $teacher, int $assignmentId): DidacticPlan
    {
        $this->actingAs($teacher)
            ->post('/planeaciones', $this->validPayload($assignmentId))
            ->assertRedirect();

        return DidacticPlan::query()->latest('id')->firstOrFail()->load('status');
    }

    protected function validPayload(int $assignmentId): array
    {
        return [
            'teacher_subject_assignment_id' => $assignmentId,
            'general_objective' => 'Objetivo general de la planeacion',
            'course_intent' => 'Intencion del curso',
            'methodology_notes' => 'Notas metodologicas',
            'general_observations' => 'Observaciones generales',
            'units' => [
                [
                    'unit_number' => 1,
                    'title' => 'Unidad 1',
                    'learning_objective' => 'Objetivo de aprendizaje',
                    'planned_hours' => 10,
                    'progress_percentage' => 100,
                    'start_week' => 1,
                    'end_week' => 2,
                    'teaching_strategy' => 'Clase guiada',
                    'learning_evidence' => 'Evidencia principal',
                    'assessment_strategy' => 'Rubrica',
                    'modules' => [
                        [
                            'module_number' => 1,
                            'title' => 'Modulo 1',
                            'topic_description' => 'Tema introductorio',
                            'theoretical_hours' => 5,
                            'practical_hours' => 5,
                            'learning_activity' => 'Actividad',
                            'teaching_activity' => 'Actividad docente',
                            'resources' => 'Laptop',
                            'assessment_activity' => 'Lista de cotejo',
                            'delivery_mode' => 'PRESENTIAL',
                            'scheduled_date' => '2026-02-01',
                        ],
                    ],
                ],
            ],
            'evaluation_criteria' => [
                [
                    'unit_number' => 1,
                    'criterion_type_code' => 'SUMMATIVE',
                    'criterion_name' => 'Proyecto integrador',
                    'description' => 'Desarrollo del producto final',
                    'evidence_description' => 'Entrega del proyecto',
                    'instrument_description' => 'Rubrica institucional',
                    'weight_percentage' => 100,
                    'minimum_score' => 70,
                    'sort_order' => 1,
                ],
            ],
            'references' => [
                [
                    'reference_type' => 'BIBLIOGRAPHY',
                    'citation' => 'Referencia institucional',
                    'sort_order' => 1,
                ],
            ],
        ];
    }
}
