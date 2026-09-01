<?php

namespace Domains\Identity\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->isRoot() || $user->isOwner());
    }

    public function rules(): array
    {
        $actor = $this->user();
        $companyId = ($actor?->isRoot() && $this->filled('company_id'))
            ? $this->integer('company_id')
            : $actor?->company_id;

        return [
            /**
             * The role's display name. Unique within its company.
             *
             * @example Editor
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->where('company_id', $companyId)->where('guard_name', 'web'),
            ],

            /**
             * Whether the role is currently assignable.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],

            /**
             * The company to create the role in. Root-only — an owner's new role is always
             * placed into the owner's own company regardless of this field. Root defaults to
             * their own company when omitted.
             *
             * @example 1
             */
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')],

            /**
             * The IDs of the permissions this role grants.
             *
             * @example [1, 2]
             */
            'permissions' => ['sometimes', 'array'],

            /**
             * A permission ID within the permissions list.
             */
            'permissions.*' => ['distinct', 'integer', Rule::exists('permissions', 'id')],
        ];
    }
}
