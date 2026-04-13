<?php

namespace App\Http\Requests\Admin;

use App\Domain\Security\Enums\RoleCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManagedUserRequest extends FormRequest
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
            'employee_number' => ['nullable', 'string', 'max:30', 'unique:users,employee_number'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', Rule::in($this->allowedRoleCodes())],
            'reviewer_career_ids' => ['nullable', 'array'],
            'reviewer_career_ids.*' => ['required', 'integer', 'exists:careers,id'],
            'must_change_password' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'employee_number' => filled($this->employee_number) ? trim((string) $this->employee_number) : null,
            'name' => trim((string) $this->name),
            'email' => strtolower(trim((string) $this->email)),
            'roles' => array_values(array_unique($this->input('roles', []))),
            'reviewer_career_ids' => array_values(array_unique(array_map(
                static fn ($careerId) => (int) $careerId,
                $this->input('reviewer_career_ids', []),
            ))),
            'must_change_password' => $this->boolean('must_change_password'),
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
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
