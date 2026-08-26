<?php

namespace Domains\Identity\Http\Requests\User;

use App\Concerns\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->isRoot() || $user->isOwner());
    }

    public function rules(): array
    {
        return [
            /**
             * The user's full name.
             *
             * @example Jane Doe
             */
            'name' => ['sometimes', 'string', 'max:255'],

            /**
             * The user's email address. Used to sign in.
             *
             * @example jane@acme-inc.com
             */
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($this->route('id'))],

            /**
             * The user's new password. Leave out to keep the current password.
             *
             * @example correct-horse-battery-staple
             */
            'password' => array_merge(['sometimes'], $this->passwordRules()),

            /**
             * The user's phone number.
             *
             * @example +1 555-0100
             */
            'phone' => ['nullable', 'string', 'max:50'],

            /**
             * The user's WhatsApp number.
             *
             * @example +1 555-0100
             */
            'whatsapp' => ['nullable', 'string', 'max:50'],
        ];
    }
}
