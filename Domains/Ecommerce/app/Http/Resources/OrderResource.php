<?php

namespace Domains\Ecommerce\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Ecommerce\Models\Order;
use Illuminate\Http\Request;

/**
 * @mixin Order
 */
class OrderResource extends BaseResource
{
    public string $routeName = 'api.ecommerce.orders';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'company_id' => $this->company_id,
            'customer_id' => $this->customer_id,

            /**
             * The customer who placed this order.
             */
            'customer' => CustomerResource::make($this->whenLoaded('customer')),

            'payment_method_id' => $this->payment_method_id,

            /**
             * The payment method selected for this order, if one has been set.
             */
            'payment_method' => PaymentMethodResource::make($this->whenLoaded('paymentMethod')),

            'coupon_id' => $this->coupon_id,

            /**
             * The coupon currently applied to this order, if any.
             */
            'coupon' => CouponResource::make($this->whenLoaded('coupon')),

            /**
             * A unique, human-readable reference for this order.
             *
             * @example ORD-20260115-A1B2C3
             */
            'order_number' => $this->order_number,

            /**
             * The order's current lifecycle state.
             */
            'status' => $this->status,

            /**
             * The sum of all line item totals, before discounts, tax, and shipping.
             *
             * @example 149.98
             */
            'subtotal' => $this->subtotal,

            /**
             * The amount deducted from the subtotal by an applied coupon.
             *
             * @example 15.00
             */
            'discount_total' => $this->discount_total,

            /**
             * The tax amount added to the order.
             *
             * @example 12.00
             */
            'tax_total' => $this->tax_total,

            /**
             * The shipping cost added to the order.
             *
             * @example 9.99
             */
            'shipping_total' => $this->shipping_total,

            /**
             * The final charged amount: subtotal minus the discount, plus tax and shipping.
             *
             * @example 156.97
             */
            'total' => $this->total,

            /**
             * The three-letter ISO 4217 currency code for the order's monetary amounts.
             *
             * @example USD
             */
            'currency' => $this->currency,

            'notes' => $this->notes,

            /**
             * When the order was cancelled, if it has been.
             *
             * @example 2026-01-20T09:15:00Z
             */
            'cancelled_at' => $this->cancelled_at,

            /**
             * The line items belonging to this order.
             *
             * @var OrderItemResource[]
             */
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ]);
    }
}
