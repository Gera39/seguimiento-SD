<?php

namespace App\Domain\Planning\Services;

use App\Models\DidacticPlan;

class DidacticPlanCommentSnapshotService
{
    public function capture(DidacticPlan $plan, ?string $fieldPath): ?string
    {
        if (blank($fieldPath)) {
            return null;
        }

        $structure = $this->planStructure($plan);
        $segments = explode('.', $fieldPath);
        $value = $structure;

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
                continue;
            }

            return null;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return $value === null ? null : (string) $value;
    }

    public function commentTargets(DidacticPlan $plan): array
    {
        $plan->loadMissing([
            'units.modules',
            'evaluationCriteria',
            'references',
        ]);

        $targets = [
            [
                'entity_type' => 'PLAN',
                'field_path' => 'general_objective',
                'field_label' => 'Plan > Objetivo general',
            ],
            [
                'entity_type' => 'PLAN',
                'field_path' => 'course_intent',
                'field_label' => 'Plan > Intencion del curso',
            ],
            [
                'entity_type' => 'PLAN',
                'field_path' => 'methodology_notes',
                'field_label' => 'Plan > Notas metodologicas',
            ],
            [
                'entity_type' => 'PLAN',
                'field_path' => 'general_observations',
                'field_label' => 'Plan > Observaciones generales',
            ],
        ];

        foreach ($plan->units->sortBy('unit_number')->values() as $unit) {
            $targets[] = [
                'entity_type' => 'UNIT',
                'field_path' => "units.{$unit->unit_number}.title",
                'field_label' => "Unidad {$unit->unit_number} > Titulo",
            ];
            $targets[] = [
                'entity_type' => 'UNIT',
                'field_path' => "units.{$unit->unit_number}.learning_objective",
                'field_label' => "Unidad {$unit->unit_number} > Objetivo de aprendizaje",
            ];
            $targets[] = [
                'entity_type' => 'UNIT',
                'field_path' => "units.{$unit->unit_number}.planned_hours",
                'field_label' => "Unidad {$unit->unit_number} > Horas planeadas",
            ];

            foreach ($unit->modules->sortBy('module_number')->values() as $module) {
                $targets[] = [
                    'entity_type' => 'MODULE',
                    'field_path' => "units.{$unit->unit_number}.modules.{$module->module_number}.title",
                    'field_label' => "Unidad {$unit->unit_number} > Modulo {$module->module_number} > Titulo",
                ];
                $targets[] = [
                    'entity_type' => 'MODULE',
                    'field_path' => "units.{$unit->unit_number}.modules.{$module->module_number}.topic_description",
                    'field_label' => "Unidad {$unit->unit_number} > Modulo {$module->module_number} > Tema",
                ];
                $targets[] = [
                    'entity_type' => 'MODULE',
                    'field_path' => "units.{$unit->unit_number}.modules.{$module->module_number}.learning_activity",
                    'field_label' => "Unidad {$unit->unit_number} > Modulo {$module->module_number} > Actividad de aprendizaje",
                ];
                $targets[] = [
                    'entity_type' => 'MODULE',
                    'field_path' => "units.{$unit->unit_number}.modules.{$module->module_number}.assessment_activity",
                    'field_label' => "Unidad {$unit->unit_number} > Modulo {$module->module_number} > Actividad de evaluacion",
                ];
            }
        }

        foreach ($plan->evaluationCriteria->sortBy('sort_order')->values() as $criterion) {
            $sortOrder = (int) $criterion->sort_order;

            $targets[] = [
                'entity_type' => 'EVALUATION',
                'field_path' => "evaluation_criteria.{$sortOrder}.criterion_name",
                'field_label' => "Evaluacion {$sortOrder} > Nombre del criterio",
            ];
            $targets[] = [
                'entity_type' => 'EVALUATION',
                'field_path' => "evaluation_criteria.{$sortOrder}.description",
                'field_label' => "Evaluacion {$sortOrder} > Descripcion",
            ];
            $targets[] = [
                'entity_type' => 'EVALUATION',
                'field_path' => "evaluation_criteria.{$sortOrder}.evidence_description",
                'field_label' => "Evaluacion {$sortOrder} > Evidencia",
            ];
            $targets[] = [
                'entity_type' => 'EVALUATION',
                'field_path' => "evaluation_criteria.{$sortOrder}.instrument_description",
                'field_label' => "Evaluacion {$sortOrder} > Instrumento",
            ];
        }

        return $targets;
    }

    protected function planStructure(DidacticPlan $plan): array
    {
        $plan->loadMissing([
            'units.modules',
            'evaluationCriteria',
            'references',
        ]);

        return [
            'general_objective' => $plan->general_objective,
            'course_intent' => $plan->course_intent,
            'methodology_notes' => $plan->methodology_notes,
            'general_observations' => $plan->general_observations,
            'units' => $plan->units
                ->sortBy('unit_number')
                ->mapWithKeys(fn ($unit) => [
                    (string) $unit->unit_number => [
                        'title' => $unit->title,
                        'learning_objective' => $unit->learning_objective,
                        'planned_hours' => (string) $unit->planned_hours,
                        'progress_percentage' => (string) $unit->progress_percentage,
                        'teaching_strategy' => $unit->teaching_strategy,
                        'learning_evidence' => $unit->learning_evidence,
                        'assessment_strategy' => $unit->assessment_strategy,
                        'modules' => $unit->modules
                            ->sortBy('module_number')
                            ->mapWithKeys(fn ($module) => [
                                (string) $module->module_number => [
                                    'title' => $module->title,
                                    'topic_description' => $module->topic_description,
                                    'learning_activity' => $module->learning_activity,
                                    'teaching_activity' => $module->teaching_activity,
                                    'resources' => $module->resources,
                                    'assessment_activity' => $module->assessment_activity,
                                ],
                            ])
                            ->all(),
                    ],
                ])
                ->all(),
            'evaluation_criteria' => $plan->evaluationCriteria
                ->sortBy('sort_order')
                ->mapWithKeys(fn ($criterion) => [
                    (string) $criterion->sort_order => [
                        'criterion_name' => $criterion->criterion_name,
                        'description' => $criterion->description,
                        'evidence_description' => $criterion->evidence_description,
                        'instrument_description' => $criterion->instrument_description,
                    ],
                ])
                ->all(),
        ];
    }
}
