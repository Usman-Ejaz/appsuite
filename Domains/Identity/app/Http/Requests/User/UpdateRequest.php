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

            /**
             * Roles to assign to this user, from the acting company's own role catalog —
             * replaces the user's entire role set. Omit to leave roles unchanged. Any id that
             * doesn't belong to that company is silently ignored rather than rejected.
             *
             * @example [3, 5]
             */
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer'],

            /**
             * App codes to individually grant this user, narrowing which of the company's
             * subscribed apps they can access — replaces the user's entire app grant set. Omit
             * to leave app grants unchanged. A code outside the company's own subscribed apps
             * is silently ignored.
             *
             * @example ["ecommerce", "hr"]
             */
            'app_codes' => ['nullable', 'array'],
            'app_codes.*' => ['string'],
        ];
    }
}
