<?php

namespace Domains\Core\Http\Requests\App;

use Domains\Core\Enums\AppSiteMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
             * The app's display name.
             *
             * @example CRM
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * A shorter display label for the app, used where space is limited.
             *
             * @example CRM
             */
            'label' => ['nullable', 'string', 'max:255'],

            /**
             * A url-friendly identifier for the app.
             *
             * @example crm
             */
            'slug' => ['required', 'string', 'max:255', Rule::unique('apps')],

            /**
             * Whether the app operates in single-tenant or multi-tenant mode.
             *
             * @example Multi
             */
            'site_mode' => ['required', Rule::enum(AppSiteMode::class)],

            /**
             * A short description of the app.
             *
             * @example Manage leads, contacts, and deals.
             */
            'description' => ['nullable', 'string', 'max:500'],

            /**
             * The app's catalog category.
             *
             * @example Sales
             */
            'category' => ['nullable', 'string', 'max:255'],

            /**
             * The app's unique code, used to key entitlements and frontend routing.
             *
             * @example crm
             */
            'code' => ['required', 'string', 'max:255', Rule::unique('apps')],

            /**
             * The app's brand color, as a hex string.
             *
             * @example #5E6AD2
             */
            'color' => ['nullable', 'string', 'max:255'],

            /**
             * The app's icon identifier.
             *
             * @example zap
             */
            'icon' => ['nullable', 'string', 'max:255'],

            /**
             * Whether the app is currently offered to companies.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],

            /**
             * When the app was released on the platform.
             *
             * @example 2026-01-01
             */
            'released_at' => ['nullable', 'date'],
        ];
    }
}
