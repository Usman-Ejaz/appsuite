<?php

namespace Domains\Ecommerce\Actions;

use Domains\Ecommerce\Enums\EcommerceTrigger;
use Domains\Ecommerce\Enums\OrderStatus;
use Domains\Ecommerce\Models\Order;
use Domains\Notification\Services\Triggers\TriggerDispatcher;

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
            if ($product = $item->product) {
                $this->adjustProductStock->handle($product, $item->quantity);
            }
        }

        if ($coupon = $order->coupon) {
            $coupon->decrement('usage_count');
        }

        $previousStatus = $order->status;

        $order->update([
            'status' => OrderStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);

        TriggerDispatcher::dispatch(EcommerceTrigger::ORDER_STATUS_CHANGED)
            ->for($order)
            ->with(['previous' => ['order.status' => $previousStatus->value]]);

        return $order->fresh();
    }
}
