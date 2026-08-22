<?php

namespace Domains\Ecommerce\Actions;

use Domains\Ecommerce\Enums\CouponType;
use Domains\Ecommerce\Models\Coupon;
use Domains\Ecommerce\Models\Order;

class ApplyCouponToOrder
{
    public function __construct(protected RecalculateOrderTotals $recalculateOrderTotals)
    {
        //
    }

    public function handle(Order $order, string $code): Order
    {
        $coupon = Coupon::query()
            ->where('company_id', $order->company_id)
            ->where('code', $code)
            ->firstOrFail();

        abort_unless($coupon->is_active, 422, 'This coupon is not active.');
        abort_if($coupon->starts_at?->isFuture(), 422, 'This coupon is not active yet.');
        abort_if($coupon->expires_at?->isPast(), 422, 'This coupon has expired.');
        abort_if($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit, 422, 'This coupon has reached its usage limit.');
        abort_if(
            $coupon->min_order_amount && bccomp((string) $order->subtotal, (string) $coupon->min_order_amount, 2) < 0,
            422,
            'This order does not meet the coupon\'s minimum order amount.'
        );

        $discount = $coupon->type === CouponType::PERCENTAGE
            ? bcdiv(bcmul((string) $order->subtotal, (string) $coupon->value, 4), '100', 2)
            : (string) $coupon->value;

        if ($coupon->max_discount_amount && bccomp($discount, (string) $coupon->max_discount_amount, 2) > 0) {
            $discount = (string) $coupon->max_discount_amount;
        }

        $order->update(['coupon_id' => $coupon->id, 'discount_total' => $discount]);
        $coupon->increment('usage_count');

        return $this->recalculateOrderTotals->handle($order);
    }
}
