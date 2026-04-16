<?php

namespace App\Http\Requests\Admin;

use App\Domain\Security\Enums\RoleCode;
use Illuminate\Foundation\Http\FormRequest;

class ValidateUserImportRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ];
    }
}
