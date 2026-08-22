<?php

namespace Domains\Ecommerce\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Ecommerce\Models\Coupon;
use Illuminate\Http\Request;

/**
 * @mixin Coupon
 */
class CouponResource extends BaseResource
{
    public string $routeName = 'api.ecommerce.coupons';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'company_id' => $this->company_id,

            /**
             * The campaign this coupon is linked to. Not set for a standalone coupon.
             */
            'campaign_id' => $this->campaign_id,

            /**
             * The campaign this coupon belongs to, when one is linked.
             */
            'campaign' => CampaignResource::make($this->whenLoaded('campaign')),

            /**
             * The code a customer enters at checkout.
             */
            'code' => $this->code,

            /**
             * How the discount is calculated.
             */
            'type' => $this->type,

            /**
             * The discount amount, as a flat currency value or a percentage of the order
             * subtotal depending on `type`.
             */
            'value' => $this->value,

            /**
             * The highest discount a `Percentage` coupon can apply. Has no effect on a
             * `Fixed` coupon.
             */
            'max_discount_amount' => $this->max_discount_amount,

            /**
             * The minimum order subtotal required for this coupon to be applied.
             */
            'min_order_amount' => $this->min_order_amount,

            /**
             * The maximum number of times this coupon can be used in total, or unlimited
             * when not set.
             */
            'usage_limit' => $this->usage_limit,

            /**
             * How many times this coupon has been used. This can't be set directly.
             */
            'usage_count' => $this->usage_count,

            /**
             * The date and time this coupon becomes usable.
             */
            'starts_at' => $this->starts_at,

            /**
             * The date and time this coupon stops being usable.
             */
            'expires_at' => $this->expires_at,

            /**
             * Whether the coupon can currently be applied to an order.
             */
            'is_active' => $this->is_active,
        ]);
    }
}
