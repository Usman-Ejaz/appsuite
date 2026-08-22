<?php

namespace Domains\Ecommerce\Actions;

use Domains\Ecommerce\Enums\OrderStatus;
use Domains\Ecommerce\Models\Order;

class CancelOrder
{
    public function __construct(protected AdjustProductStock $adjustProductStock)
    {
        //
    }

    public function handle(Order $order): Order
    {
        abort_if($order->status === OrderStatus::CANCELLED, 422, 'This order is already cancelled.');

        foreach ($order->items as $item) {
            if ($product = $item->ecommerceProduct) {
                $this->adjustProductStock->handle($product, $item->quantity);
            }
        }

        if ($coupon = $order->coupon) {
            $coupon->decrement('usage_count');
        }

        $order->update([
            'status' => OrderStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);

        return $order->fresh();
    }
}
