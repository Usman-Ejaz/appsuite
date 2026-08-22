<?php

namespace Domains\Ecommerce\Actions;

use Domains\Ecommerce\Models\OrderItem;

class RemoveOrderItem
{
    public function __construct(
        protected AdjustProductStock $adjustProductStock,
        protected RecalculateOrderTotals $recalculateOrderTotals,
    ) {
        //
    }

    public function handle(OrderItem $item): void
    {
        $order = $item->order;

        if ($product = $item->ecommerceProduct) {
            $this->adjustProductStock->handle($product, $item->quantity);
        }

        $item->delete();

        $this->recalculateOrderTotals->handle($order);
    }
}
