<?php

namespace Domains\Ecommerce\Actions;

use Domains\Ecommerce\Models\OrderItem;

class UpdateOrderItemQuantity
{
    public function __construct(
        protected AdjustProductStock $adjustProductStock,
        protected RecalculateOrderTotals $recalculateOrderTotals,
    ) {
        //
    }

    /**
     * Only `quantity` is mutable on a line item — name/sku/price are
     * immutable purchase-time snapshots. Stock is adjusted by the delta
     * between the old and new quantity, not the new quantity outright.
     */
    public function handle(OrderItem $item, int $quantity): OrderItem
    {
        $delta = $item->quantity - $quantity;
        $product = $item->product;

        if ($product) {
            abort_if(
                $product->track_inventory && $delta < 0 && $product->stock_quantity < abs($delta),
                422,
                'Not enough stock available for this product.'
            );

            $this->adjustProductStock->handle($product, $delta);
        }

        $item->update([
            'quantity' => $quantity,
            'subtotal' => bcmul((string) $item->unit_price, (string) $quantity, 2),
        ]);

        $this->recalculateOrderTotals->handle($item->order);

        return $item->fresh();
    }
}
