<?php

namespace App\Http\Requests\Admin;

use App\Domain\Security\Enums\RoleCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManagedUserRolesRequest extends FormRequest
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
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', Rule::in($this->allowedRoleCodes())],
            'reviewer_career_ids' => ['nullable', 'array'],
            'reviewer_career_ids.*' => ['required', 'integer', 'exists:careers,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'roles' => array_values(array_unique($this->input('roles', []))),
            'reviewer_career_ids' => array_values(array_unique(array_map(
                static fn ($careerId) => (int) $careerId,
                $this->input('reviewer_career_ids', []),
            ))),
        ]);
    }

    protected function allowedRoleCodes(): array
    {
        $user = $this->user();

        if ($user?->hasRole(RoleCode::ADMIN)) {
            return array_map(
                fn (RoleCode $roleCode) => $roleCode->value,
                RoleCode::cases(),
            );
        }

        return [
            RoleCode::DOCENTE->value,
            RoleCode::REVISOR->value,
        ];
    }
}
