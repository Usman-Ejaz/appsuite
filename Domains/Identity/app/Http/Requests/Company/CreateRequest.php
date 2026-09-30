<?php

namespace Domains\Identity\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRoot() ?? false;
    }

    public function rules(): array
    {
        return [
            /**
             * The company's display name.
             *
             * @example Acme Inc
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * A url-friendly identifier for the company.
             *
             * @example acme-inc
             */
            'slug' => ['required', 'string', 'max:255', Rule::unique('companies')],

            /**
             * A short description of the company.
             *
             * @example A trusted supplier of outdoor equipment.
             */
            'description' => ['nullable', 'string', 'max:500'],

            /**
             * The company's mailing or business address.
             *
             * @example 123 Market Street, San Francisco, CA
             */
            'address' => ['nullable', 'string', 'max:500'],

            /**
             * The company's registered business license number.
             *
             * @example LIC-12345678
             */
            'license_number' => ['nullable', 'string', 'max:255'],

            /**
             * The IDs of the apps this company is subscribed to. Every company must be
             * subscribed to at least one app from the moment it's created.
             *
             * @example [1, 2]
             */
            'apps' => ['required', 'array', 'min:1'],

            /**
             * An app ID within the apps list.
             */
            'apps.*' => ['distinct', 'integer', Rule::exists('apps', 'id')],

            /**
             * The primary owner user to provision for this company. Every company must have an
             * owner from the moment it's created.
             */
            'owner' => ['required', 'array'],

            /**
             * The owner's full name.
             *
             * @example Aria Steinberg
             */
            'owner.name' => ['required', 'string', 'max:255'],

            /**
             * The owner's email address. Used to sign in.
             *
             * @example aria@acme-inc.com
             */
            'owner.email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],

            /**
             * The owner's phone number.
             *
             * @example +1 555-0100
             */
            'owner.phone' => ['nullable', 'string', 'max:50'],

            /**
             * The owner's WhatsApp number.
             *
             * @example +1 555-0100
             */
            'owner.whatsapp' => ['nullable', 'string', 'max:50'],

            /**
             * The owner's initial password. Not required to be confirmed since it's set by the
             * provisioning root user on the owner's behalf, not typed by the owner themself.
             *
             * @example correct-horse-battery-staple
             */
            'owner.password' => ['required', 'string', Password::default()],
        ];
    }
}
