<?php

namespace Domains\Ecommerce\Http\Requests\Review;

use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Enums\ReviewStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ModerateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::REVIEW_MODERATE->value);
    }

    /**
     * `Pending` is deliberately excluded, since moderation is a one-way
     * decision, not a resettable field.
     */
    public function rules(): array
    {
        return [
            /**
             * The moderation decision for the review. `Pending` isn't accepted here, since it's a one-way decision.
             *
             * @example Approved
             */
            'status' => ['required', Rule::enum(ReviewStatus::class)->only([ReviewStatus::APPROVED, ReviewStatus::REJECTED])],
        ];
    }
}
