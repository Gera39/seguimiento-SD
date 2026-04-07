<?php

namespace App\Http\Requests\Planning;

use Illuminate\Foundation\Http\FormRequest;

class AuthorizeDidacticPlanRequest extends FormRequest
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
            'general_comments' => ['nullable', 'string', 'max:1500'],
        ];
    }
}
