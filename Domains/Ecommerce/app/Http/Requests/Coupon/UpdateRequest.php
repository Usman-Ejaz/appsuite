<?php

namespace Domains\Ecommerce\Http\Requests\Coupon;

use Domains\Ecommerce\Enums\CouponType;
use Domains\Ecommerce\Enums\EcommercePermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::COUPON_UPDATE->value);
    }

    public function rules(): array
    {
        $companyId = $this->user()?->getCompanyId();

        return [
            /**
             * The marketing campaign this coupon is linked to. Standalone codes without a
             * campaign are common and fully supported.
             *
             * @example 4
             */
            'campaign_id' => ['nullable', 'integer', Rule::exists('campaigns', 'id')->where('company_id', $companyId)],

            /**
             * The unique code a customer enters at checkout.
             *
             * @example SAVE10
             */
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('coupons')->where('company_id', $companyId)->ignore($this->route('id'))],

            /**
             * How the coupon's discount is calculated.
             *
             * @example Fixed
             */
            'type' => ['sometimes', Rule::enum(CouponType::class)],

            /**
             * The discount amount. A flat currency value when `type` is `Fixed`, or a
             * percentage of the order subtotal when `type` is `Percentage`.
             *
             * @example 10
             */
            'value' => ['sometimes', 'numeric', 'min:0'],

            /**
             * The highest discount a `Percentage` coupon can apply, regardless of the order
             * subtotal. Has no effect on a `Fixed` coupon.
             *
             * @example 25
             */
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],

            /**
             * The minimum order subtotal required for this coupon to be applied.
             *
             * @example 50
             */
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],

            /**
             * The maximum number of times this coupon can be used in total, or unlimited
             * when not set.
             *
             * @example 100
             */
            'usage_limit' => ['nullable', 'integer', 'min:1'],

            /**
             * The date and time this coupon becomes usable. Leave blank for immediate
             * availability.
             *
             * @example 2026-09-01T00:00:00Z
             */
            'starts_at' => ['nullable', 'date'],

            /**
             * The date and time this coupon stops being usable. Leave blank for a coupon
             * that never expires.
             *
             * @example 2026-12-31T23:59:59Z
             */
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],

            /**
             * Whether the coupon can currently be applied to an order.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
