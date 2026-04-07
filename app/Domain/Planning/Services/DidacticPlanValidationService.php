<?php

namespace App\Domain\Planning\Services;

use App\Domain\Planning\Enums\PlanningValidationContext;
use App\Models\DidacticPlan;
use App\Models\DidacticPlanValidationSnapshot;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DidacticPlanValidationService
{
    public function validateAndCapture(
        DidacticPlan $plan,
        PlanningValidationContext $context,
        User $user,
    ): array {
        $snapshot = $this->buildSnapshot($plan, $context);

        if (! $snapshot['is_valid']) {
            throw ValidationException::withMessages($snapshot['errors']);
        }

        DidacticPlanValidationSnapshot::query()->create([
            'didactic_plan_id' => $plan->id,
            'validation_context' => $context->value,
            'total_units' => $snapshot['total_units'],
            'total_modules' => $snapshot['total_modules'],
            'total_unit_hours' => $snapshot['total_unit_hours'],
            'total_module_hours' => $snapshot['total_module_hours'],
            'total_progress_percentage' => $snapshot['total_progress_percentage'],
            'total_evaluation_percentage' => $snapshot['total_evaluation_percentage'],
            'is_valid' => true,
            'validation_details' => json_encode($snapshot['details'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'created_by_user_id' => $user->id,
            'created_at' => now(),
        ]);

        return $snapshot;
    }

    public function buildSnapshot(
        DidacticPlan $plan,
        PlanningValidationContext $context,
    ): array {
        $plan->loadMissing([
            'units.modules',
            'evaluationCriteria',
        ]);

        $units = $plan->units->sortBy('unit_number')->values();
        $criteria = $plan->evaluationCriteria->sortBy('sort_order')->values();

        $totalUnits = $units->count();
        $totalModules = $units->sum(fn ($unit) => $unit->modules->count());
        $totalUnitHours = round((float) $units->sum('planned_hours'), 2);
        $totalModuleHours = round((float) $units->sum(
            fn ($unit) => $unit->modules->sum(fn ($module) => (float) $module->theoretical_hours + (float) $module->practical_hours)
        ), 2);
        $totalProgressPercentage = round((float) $units->sum('progress_percentage'), 2);
        $totalEvaluationPercentage = round((float) $criteria->sum('weight_percentage'), 2);

        $errors = [];

        foreach ($units as $unit) {
            $moduleHours = round((float) $unit->modules->sum(
                fn ($module) => (float) $module->theoretical_hours + (float) $module->practical_hours
            ), 2);

            if ($unit->modules->isEmpty()) {
                $errors["units.{$unit->unit_number}.modules"] = "La unidad {$unit->unit_number} debe tener al menos un modulo.";
            }

            if (round((float) $unit->planned_hours, 2) !== $moduleHours) {
                $errors["units.{$unit->unit_number}.planned_hours"] = "La unidad {$unit->unit_number} no coincide con la suma de horas de sus modulos.";
            }
        }

        foreach ($criteria as $index => $criterion) {
            if (blank($criterion->description) || blank($criterion->evidence_description) || blank($criterion->instrument_description)) {
                $errors["evaluation_criteria.{$index}.coherence"] = 'Cada criterio de evaluacion debe incluir descripcion, evidencia e instrumento.';
            }
        }

        if ($totalProgressPercentage > 100) {
            $errors['units_progress'] = 'El porcentaje total de avance no puede exceder 100.';
        }

        if ($totalEvaluationPercentage > 100) {
            $errors['evaluation_percentage'] = 'La suma de criterios de evaluacion no puede exceder 100.';
        }

        if (in_array($context, [PlanningValidationContext::SUBMISSION, PlanningValidationContext::AUTHORIZATION], true)) {
            if ($totalUnits === 0) {
                $errors['units'] = 'La planeacion debe incluir al menos una unidad.';
            }

            if ($totalModules === 0) {
                $errors['modules'] = 'La planeacion debe incluir al menos un modulo.';
            }

            if ($criteria->isEmpty()) {
                $errors['evaluation_criteria'] = 'La planeacion debe incluir al menos un criterio de evaluacion.';
            }

            if ($totalProgressPercentage !== 100.0) {
                $errors['units_progress_exact'] = 'La suma del avance por unidad debe ser exactamente 100 para enviar o autorizar.';
            }

            if ($totalEvaluationPercentage !== 100.0) {
                $errors['evaluation_percentage_exact'] = 'La suma de criterios de evaluacion debe ser exactamente 100 para enviar o autorizar.';
            }

            if ($totalUnitHours !== $totalModuleHours) {
                $errors['hours_consistency'] = 'Las horas programadas por unidad deben coincidir exactamente con la suma de horas de modulos.';
            }
        }

        return [
            'total_units' => $totalUnits,
            'total_modules' => $totalModules,
            'total_unit_hours' => $totalUnitHours,
            'total_module_hours' => $totalModuleHours,
            'total_progress_percentage' => $totalProgressPercentage,
            'total_evaluation_percentage' => $totalEvaluationPercentage,
            'is_valid' => $errors === [],
            'errors' => $errors,
            'details' => [
                'context' => $context->value,
                'errors' => $errors,
            ],
        ];
    }
}
