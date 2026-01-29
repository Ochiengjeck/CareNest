<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

trait UserValidationRules
{
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    protected function userRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
        ];
    }

    protected function roleRules(): array
    {
        return [
            'selectedRoles' => ['required', 'array', 'min:1'],
            'selectedRoles.*' => ['string', 'exists:roles,name'],
        ];
    }

    protected function permissionRules(): array
    {
        return [
            'directPermissions' => ['array'],
            'directPermissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
