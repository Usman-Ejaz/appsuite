<?php

namespace Domains\Ecommerce\Actions;

use Domains\Ecommerce\Models\Order;

class RecalculateOrderTotals
{
    /**
     * Recomputes `subtotal` from the order's line items and rolls it up
     * into `total` alongside the already-set discount/tax/shipping totals.
     * Uses bcmath throughout — the decimal-cast columns come back as
     * strings specifically to avoid float precision loss, and plain `+`/`-`
     * would silently reintroduce it.
     */
    public function handle(Order $order): Order
    {
        $subtotal = (string) $order->items()->sum('subtotal');

        $total = bcsub($subtotal, (string) $order->discount_total, 2);
        $total = bcadd($total, (string) $order->tax_total, 2);
        $total = bcadd($total, (string) $order->shipping_total, 2);

        $order->update([
            'subtotal' => $subtotal,
            'total' => $total,
        ]);

        return $order->fresh();
    }
}
