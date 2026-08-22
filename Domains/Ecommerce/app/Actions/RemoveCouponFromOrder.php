<?php

namespace Domains\Ecommerce\Actions;

use Domains\Ecommerce\Models\Order;

class RemoveCouponFromOrder
{
    public function __construct(protected RecalculateOrderTotals $recalculateOrderTotals)
    {
        //
    }

    public function handle(Order $order): Order
    {
        if ($coupon = $order->coupon) {
            $coupon->decrement('usage_count');
        }

        $order->update(['coupon_id' => null, 'discount_total' => 0]);

        return $this->recalculateOrderTotals->handle($order);
    }
}
