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

class DidacticPlanWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_docente_can_create_and_submit_a_plan(): void
    {
        $this->seedCatalogs();

        $teacher = User::factory()->create();
        $this->assignRole($teacher, RoleCode::DOCENTE);
        $assignment = $this->createAssignmentForTeacher($teacher);

        $this->actingAs($teacher)
            ->post('/planeaciones', $this->validPayload($assignment->id))
            ->assertRedirect();

        $plan = DidacticPlan::query()->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('plans.submit', $plan, absolute: false))
            ->assertRedirect(route('plans.show', $plan, absolute: false));

        $plan->refresh();

        $this->assertSame(PlanningStatusCode::SUBMITTED->value, $plan->status->code);
        $this->assertNotNull($plan->submitted_at);
        $this->assertDatabaseCount('didactic_plan_validation_snapshots', 2);
    }

    public function test_submitted_plan_can_not_be_updated_by_docente(): void
    {
        $this->seedCatalogs();

        $teacher = User::factory()->create();
        $this->assignRole($teacher, RoleCode::DOCENTE);
        $assignment = $this->createAssignmentForTeacher($teacher);
        $plan = $this->createPlan($teacher, $assignment->id);

        $this->actingAs($teacher)
            ->post(route('plans.submit', $plan, absolute: false))
            ->assertRedirect();

        $response = $this->actingAs($teacher)
            ->patch(route('plans.update', $plan, absolute: false), $this->validPayload($assignment->id));

        $response->assertStatus(423);
    }

    public function test_revisor_can_start_review_and_return_feedback(): void
    {
        $this->seedCatalogs();

        $teacher = User::factory()->create();
        $revisor = User::factory()->create();

        $this->assignRole($teacher, RoleCode::DOCENTE);
        $this->assignRole($revisor, RoleCode::REVISOR);

        $assignment = $this->createAssignmentForTeacher($teacher);
        $plan = $this->createPlan($teacher, $assignment->id);

        $this->actingAs($teacher)
            ->post(route('plans.submit', $plan, absolute: false))
            ->assertRedirect();

        $this->actingAs($revisor)
            ->post(route('plans.start-review', $plan, absolute: false))
            ->assertRedirect();

        $this->actingAs($revisor)
            ->post(route('plans.feedback', $plan, absolute: false), [
                'general_comments' => 'Es necesario ajustar la evidencia final.',
                'review_comments' => [
                    [
                        'entity_type' => 'PLAN',
                        'entity_id' => null,
                        'field_path' => 'general_objective',
                        'field_label' => 'Plan > Objetivo general',
                        'severity_code' => 'REQUIRED',
                        'comment_text' => 'Alinea el instrumento de evaluacion con la evidencia.',
                    ],
                ],
            ])
            ->assertRedirect(route('plans.show', $plan, absolute: false));

        $plan->refresh();

        $this->assertSame(PlanningStatusCode::FEEDBACK->value, $plan->status->code);
        $this->assertDatabaseHas('didactic_plan_reviews', [
            'didactic_plan_id' => $plan->id,
            'review_stage_code' => 'TECHNICAL',
        ]);
        $this->assertDatabaseHas('didactic_plan_review_comments', [
            'comment_text' => 'Alinea el instrumento de evaluacion con la evidencia.',
            'field_path' => 'general_objective',
            'observed_value_snapshot' => 'Objetivo general de la planeacion',
        ]);
    }

    public function test_docente_can_respond_to_review_comment_with_updated_snapshot(): void
    {
        $this->seedCatalogs();

        $teacher = User::factory()->create();
        $reviewer = User::factory()->create();

        $this->assignRole($teacher, RoleCode::DOCENTE);
        $this->assignRole($reviewer, RoleCode::REVISOR);

        $assignment = $this->createAssignmentForTeacher($teacher);
        $plan = $this->createPlan($teacher, $assignment->id);

        $this->actingAs($teacher)
            ->post(route('plans.submit', $plan, absolute: false))
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->post(route('plans.feedback', $plan, absolute: false), [
                'general_comments' => 'Ajustar el objetivo general.',
                'review_comments' => [
                    [
                        'entity_type' => 'PLAN',
                        'entity_id' => null,
                        'field_path' => 'general_objective',
                        'field_label' => 'Plan > Objetivo general',
                        'severity_code' => 'REQUIRED',
                        'comment_text' => 'Define el objetivo con un verbo observable.',
                    ],
                ],
            ])
            ->assertRedirect();

        $plan->refresh();

        $this->actingAs($teacher)
            ->patch(route('plans.update', $plan, absolute: false), [
                ...$this->validPayload($assignment->id),
                'general_objective' => 'Desarrollar un prototipo funcional con evidencia medible.',
            ])
            ->assertRedirect();

        $commentId = \App\Models\DidacticPlanReviewComment::query()->value('id');

        $this->actingAs($teacher)
            ->patch(route('plans.comments.respond', ['didacticPlan' => $plan, 'comment' => $commentId], absolute: false), [
                'teacher_response' => 'Se reescribio el objetivo general con un verbo observable y un entregable concreto.',
            ])
            ->assertRedirect(route('plans.show', $plan, absolute: false));

        $this->assertDatabaseHas('didactic_plan_review_comments', [
            'id' => $commentId,
            'comment_status_code' => 'ADDRESSED',
            'teacher_response' => 'Se reescribio el objetivo general con un verbo observable y un entregable concreto.',
            'updated_value_snapshot' => 'Desarrollar un prototipo funcional con evidencia medible.',
        ]);
    }

    public function test_directivo_can_authorize_a_plan_in_review(): void
    {
        $this->seedCatalogs();

        $teacher = User::factory()->create();
        $revisor = User::factory()->create();
        $directivo = User::factory()->create();

        $this->assignRole($teacher, RoleCode::DOCENTE);
        $this->assignRole($revisor, RoleCode::REVISOR);
        $this->assignRole($directivo, RoleCode::DIRECTIVO);

        $assignment = $this->createAssignmentForTeacher($teacher);
        $plan = $this->createPlan($teacher, $assignment->id);

        $this->actingAs($teacher)
            ->post(route('plans.submit', $plan, absolute: false))
            ->assertRedirect();

        $this->actingAs($revisor)
            ->post(route('plans.start-review', $plan, absolute: false))
            ->assertRedirect();

        $this->actingAs($directivo)
            ->post(route('plans.authorize', $plan, absolute: false), [
                'general_comments' => 'Cumple con los criterios institucionales.',
            ])
            ->assertRedirect(route('plans.show', $plan, absolute: false));

        $plan->refresh();

        $this->assertSame(PlanningStatusCode::AUTHORIZED->value, $plan->status->code);
        $this->assertNotNull($plan->authorized_at);
        $this->assertDatabaseHas('didactic_plan_reviews', [
            'didactic_plan_id' => $plan->id,
            'review_stage_code' => 'FINAL',
        ]);
    }

    public function test_revisor_can_validate_and_reopen_an_addressed_comment(): void
    {
        $this->seedCatalogs();

        $teacher = User::factory()->create();
        $reviewer = User::factory()->create();

        $this->assignRole($teacher, RoleCode::DOCENTE);
        $this->assignRole($reviewer, RoleCode::REVISOR);

        $assignment = $this->createAssignmentForTeacher($teacher);
        $plan = $this->createPlan($teacher, $assignment->id);

        $this->actingAs($teacher)
            ->post(route('plans.submit', $plan, absolute: false))
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->post(route('plans.feedback', $plan, absolute: false), [
                'general_comments' => 'Ajustar el objetivo general.',
                'review_comments' => [
                    [
                        'entity_type' => 'PLAN',
                        'entity_id' => null,
                        'field_path' => 'general_objective',
                        'field_label' => 'Plan > Objetivo general',
                        'severity_code' => 'REQUIRED',
                        'comment_text' => 'Define el objetivo con un verbo observable.',
                    ],
                ],
            ])
            ->assertRedirect();

        $commentId = \App\Models\DidacticPlanReviewComment::query()->value('id');

        $this->actingAs($teacher)
            ->patch(route('plans.update', $plan, absolute: false), [
                ...$this->validPayload($assignment->id),
                'general_objective' => 'Construir un sistema funcional con criterios medibles.',
            ])
            ->assertRedirect();

        $this->actingAs($teacher)
            ->patch(route('plans.comments.respond', ['didacticPlan' => $plan, 'comment' => $commentId], absolute: false), [
                'teacher_response' => 'Se ajusto el objetivo con un verbo observable.',
            ])
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->post(route('plans.comments.resolve', ['didacticPlan' => $plan, 'comment' => $commentId], absolute: false))
            ->assertRedirect(route('plans.review.show', $plan, absolute: false));

        $this->assertDatabaseHas('didactic_plan_review_comments', [
            'id' => $commentId,
            'comment_status_code' => 'RESOLVED',
            'is_resolved' => true,
            'validated_by_user_id' => $reviewer->id,
        ]);

        $this->actingAs($reviewer)
            ->post(route('plans.comments.reopen', ['didacticPlan' => $plan, 'comment' => $commentId], absolute: false))
            ->assertRedirect(route('plans.review.show', $plan, absolute: false));

        $this->assertDatabaseHas('didactic_plan_review_comments', [
            'id' => $commentId,
            'comment_status_code' => 'REOPENED',
            'is_resolved' => false,
            'validated_by_user_id' => null,
        ]);
    }

    public function test_user_can_export_plan_to_word_document(): void
    {
        $this->seedCatalogs();

        $teacher = User::factory()->create();
        $this->assignRole($teacher, RoleCode::DOCENTE);
        $assignment = $this->createAssignmentForTeacher($teacher);
        $plan = $this->createPlan($teacher, $assignment->id);

        $response = $this->actingAs($teacher)
            ->get(route('plans.export-word', $plan, absolute: false));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('.docx', $response->headers->get('content-disposition', ''));
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

    protected function createAssignmentForTeacher(User $teacher): TeacherSubjectAssignment
    {
        $career = Career::query()->create([
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

        return DidacticPlan::query()->firstOrFail()->load('status');
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
                    'description' => 'Entrega final del modulo',
                    'evidence_description' => 'Aplicacion funcional',
                    'instrument_description' => 'Rubrica analitica',
                    'weight_percentage' => 100,
                    'minimum_score' => 70,
                    'sort_order' => 1,
                ],
            ],
            'references' => [
                [
                    'reference_type' => 'BIBLIOGRAPHY',
                    'citation' => 'Referencia base',
                    'sort_order' => 1,
                ],
            ],
        ];
    }
}
