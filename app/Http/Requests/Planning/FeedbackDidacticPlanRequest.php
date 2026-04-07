<?php

namespace App\Http\Requests\Planning;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedbackDidacticPlanRequest extends FormRequest
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
            'general_comments' => ['required', 'string', 'max:1500'],
            'review_comments' => ['required', 'array', 'min:1'],
            'review_comments.*.entity_type' => ['required', Rule::in(['PLAN', 'UNIT', 'MODULE', 'EVALUATION'])],
            'review_comments.*.entity_id' => ['nullable', 'integer'],
            'review_comments.*.severity_code' => ['required', Rule::in(['INFO', 'WARNING', 'REQUIRED'])],
            'review_comments.*.comment_text' => ['required', 'string', 'max:1000'],
        ];
    }
}
