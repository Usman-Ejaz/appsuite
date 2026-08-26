<?php

namespace Domains\Identity\Http\Requests\Company;

use Domains\Identity\Enums\CompanyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],

            /**
             * A url-friendly identifier for the company.
             *
             * @example acme-inc
             */
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('companies')->ignore($this->route('id'))],

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
             * The company's platform status.
             *
             * @example Active
             */
            'status' => ['sometimes', Rule::enum(CompanyStatus::class)],

            /**
             * The IDs of the apps this company is subscribed to. Replaces the company's current
             * app subscriptions entirely when present — a company must always have at least one
             * app.
             *
             * @example [1, 2]
             */
            'apps' => ['sometimes', 'array', 'min:1'],

            /**
             * An app ID within the apps list.
             */
            'apps.*' => ['distinct', 'integer', Rule::exists('apps', 'id')],
        ];
    }
}
