<?php

namespace Domains\Ecommerce\Http\Requests\Campaign;

use Domains\Ecommerce\Enums\CampaignStatus;
use Domains\Ecommerce\Enums\EcommercePermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::CAMPAIGN_UPDATE->value);
    }

    public function rules(): array
    {
        return [
            /**
             * The campaign's display name.
             *
             * @example Black Friday 2026
             */
            'name' => ['sometimes', 'string', 'max:255'],

            /**
             * A unique, URL-friendly identifier for the campaign.
             *
             * @example black-friday-2026
             */
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('campaigns')->where('company_id', $this->user()?->getCompanyId())->ignore($this->route('id'))],

            /**
             * A longer description of the campaign.
             *
             * @example Storewide discounts for Black Friday weekend.
             */
            'description' => ['nullable', 'string'],

            /**
             * The campaign's publication state.
             *
             * @example Active
             */
            'status' => ['nullable', Rule::enum(CampaignStatus::class)],

            /**
             * When the campaign should start running.
             *
             * @example 2026-11-27T00:00:00Z
             */
            'starts_at' => ['nullable', 'date'],

            /**
             * When the campaign should stop running. Must be on or after `starts_at`.
             *
             * @example 2026-11-30T23:59:59Z
             */
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
