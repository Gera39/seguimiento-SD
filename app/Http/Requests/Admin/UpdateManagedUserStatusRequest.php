<?php

namespace App\Http\Requests\Admin;

use App\Domain\Security\Enums\RoleCode;
use Illuminate\Foundation\Http\FormRequest;

class UpdateManagedUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && ($user->hasRole(RoleCode::ADMIN) || $user->hasRole(RoleCode::DIRECTIVO));
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
