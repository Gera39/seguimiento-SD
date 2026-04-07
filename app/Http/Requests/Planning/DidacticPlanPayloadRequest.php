<?php

namespace App\Http\Requests\Planning;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class DidacticPlanPayloadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'teacher_subject_assignment_id' => [
                'required',
                'integer',
                Rule::exists('teacher_subject_assignments', 'id')->where(
                    fn ($query) => $query
                        ->where('teacher_user_id', $this->user()?->id)
                        ->where('is_active', true)
                ),
            ],
            'general_objective' => ['required', 'string', 'max:1000'],
            'course_intent' => ['nullable', 'string', 'max:1000'],
            'methodology_notes' => ['nullable', 'string', 'max:1000'],
            'general_observations' => ['nullable', 'string', 'max:1000'],
            'units' => ['required', 'array', 'min:1'],
            'units.*.unit_number' => ['required', 'integer', 'min:1', 'distinct'],
            'units.*.title' => ['required', 'string', 'max:200'],
            'units.*.learning_objective' => ['required', 'string', 'max:1000'],
            'units.*.planned_hours' => ['required', 'numeric', 'min:0.01'],
            'units.*.progress_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'units.*.start_week' => ['nullable', 'integer', 'min:1'],
            'units.*.end_week' => ['nullable', 'integer', 'min:1'],
            'units.*.teaching_strategy' => ['nullable', 'string', 'max:1000'],
            'units.*.learning_evidence' => ['nullable', 'string', 'max:1000'],
            'units.*.assessment_strategy' => ['nullable', 'string', 'max:1000'],
            'units.*.modules' => ['required', 'array', 'min:1'],
            'units.*.modules.*.module_number' => ['required', 'integer', 'min:1'],
            'units.*.modules.*.title' => ['required', 'string', 'max:200'],
            'units.*.modules.*.topic_description' => ['required', 'string', 'max:1000'],
            'units.*.modules.*.theoretical_hours' => ['required', 'numeric', 'min:0'],
            'units.*.modules.*.practical_hours' => ['required', 'numeric', 'min:0'],
            'units.*.modules.*.learning_activity' => ['nullable', 'string', 'max:1000'],
            'units.*.modules.*.teaching_activity' => ['nullable', 'string', 'max:1000'],
            'units.*.modules.*.resources' => ['nullable', 'string', 'max:1000'],
            'units.*.modules.*.assessment_activity' => ['nullable', 'string', 'max:1000'],
            'units.*.modules.*.delivery_mode' => ['required', Rule::in(['PRESENTIAL', 'VIRTUAL', 'HYBRID'])],
            'units.*.modules.*.scheduled_date' => ['nullable', 'date'],
            'evaluation_criteria' => ['required', 'array', 'min:1'],
            'evaluation_criteria.*.unit_number' => ['nullable', 'integer', 'min:1'],
            'evaluation_criteria.*.criterion_type_code' => ['required', Rule::exists('evaluation_criterion_types', 'code')],
            'evaluation_criteria.*.criterion_name' => ['required', 'string', 'max:150'],
            'evaluation_criteria.*.description' => ['required', 'string', 'max:500'],
            'evaluation_criteria.*.evidence_description' => ['required', 'string', 'max:500'],
            'evaluation_criteria.*.instrument_description' => ['required', 'string', 'max:250'],
            'evaluation_criteria.*.weight_percentage' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'evaluation_criteria.*.minimum_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'evaluation_criteria.*.sort_order' => ['nullable', 'integer', 'min:1'],
            'references' => ['nullable', 'array'],
            'references.*.reference_type' => ['required_with:references', Rule::in(['BIBLIOGRAPHY', 'WEBGRAPHY', 'RESOURCE'])],
            'references.*.citation' => ['required_with:references', 'string', 'max:1000'],
            'references.*.sort_order' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $units = collect($this->input('units', []));
            $unitNumbers = $units->pluck('unit_number')->filter()->all();

            foreach ($units as $unitIndex => $unit) {
                $moduleNumbers = collect($unit['modules'] ?? [])->pluck('module_number');

                if ($moduleNumbers->duplicates()->isNotEmpty()) {
                    $validator->errors()->add("units.$unitIndex.modules", 'Los numeros de modulo deben ser unicos dentro de cada unidad.');
                }

                if (
                    isset($unit['start_week'], $unit['end_week'])
                    && (int) $unit['end_week'] < (int) $unit['start_week']
                ) {
                    $validator->errors()->add("units.$unitIndex.end_week", 'La semana final no puede ser menor que la inicial.');
                }
            }

            foreach ($this->input('evaluation_criteria', []) as $criterionIndex => $criterion) {
                if (
                    isset($criterion['unit_number'])
                    && ! in_array((int) $criterion['unit_number'], array_map('intval', $unitNumbers), true)
                ) {
                    $validator->errors()->add("evaluation_criteria.$criterionIndex.unit_number", 'El criterio referencia una unidad que no existe en la planeacion.');
                }
            }
        });
    }
}
