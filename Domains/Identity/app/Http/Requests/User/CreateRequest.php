<?php

namespace Domains\Identity\Http\Requests\User;

use App\Concerns\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],

            /**
             * The user's email address. Used to sign in.
             *
             * @example jane@acme-inc.com
             */
            'email' => ['required', 'email', 'max:255', Rule::unique('users')],

            /**
             * The user's password.
             *
             * @example correct-horse-battery-staple
             */
            'password' => $this->passwordRules(),

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
             * The company to create the user in. Root-only — an owner's new user is always
             * placed into the owner's own company regardless of this field. Root defaults to
             * their own company when omitted.
             *
             * @example 1
             */
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')],

            /**
             * Roles to assign to the new user, from the acting company's own role catalog.
             * Any id that doesn't belong to that company is silently ignored rather than
             * rejected.
             *
             * @example [3, 5]
             */
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer'],

            /**
             * App codes to individually grant this user, narrowing which of the company's
             * subscribed apps they can access. Omit entirely to grant every app the company
             * subscribes to (the default). A code outside the company's own subscribed apps is
             * silently ignored.
             *
             * @example ["ecommerce", "hr"]
             */
            'app_codes' => ['nullable', 'array'],
            'app_codes.*' => ['string'],
        ];
    }
}
