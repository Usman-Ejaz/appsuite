<?php

namespace Domains\Identity\Http\Requests\Role;

use Domains\Identity\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->isRoot() || $user->isOwner());
    }

    public function rules(): array
    {
        // A role's company never changes on update, so the uniqueness check is scoped to
        // whatever company the target role already belongs to.
        $companyId = Role::find($this->route('id'))?->company_id;

        return [
            /**
             * The role's display name. Unique within its company.
             *
             * @example Editor
             */
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('roles')
                    ->where(fn ($query) => $query->where('company_id', $companyId)->where('guard_name', 'web'))
                    ->ignore($this->route('id')),
            ],

            /**
             * Whether the role is currently assignable.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],

            /**
             * The IDs of the permissions this role grants. Replaces the role's current
             * permissions entirely when present.
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
