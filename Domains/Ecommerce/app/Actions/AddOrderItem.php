<?php

namespace Domains\Ecommerce\Actions;

use Domains\Ecommerce\Models\Order;
use Domains\Ecommerce\Models\OrderItem;
use Domains\Shared\Models\Product;

class AddOrderItem
{
    public function __construct(
        protected AdjustProductStock $adjustProductStock,
        protected RecalculateOrderTotals $recalculateOrderTotals,
    ) {
        //
    }

    /**
     * Snapshots the product's current name/sku/price onto the line item —
     * see Order/OrderItem migration notes: a historical order must keep
     * showing what was actually charged even if the product changes later.
     */
    public function handle(Order $order, Product $product, int $quantity): OrderItem
    {
        abort_if(
            $product->track_inventory && $product->stock_quantity < $quantity,
            422,
            'Not enough stock available for this product.'
        );

        $item = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_price' => $product->selling_price,
            'quantity' => $quantity,
            'subtotal' => bcmul((string) $product->selling_price, (string) $quantity, 2),
        ]);

        $this->adjustProductStock->handle($product, -$quantity);
        $this->recalculateOrderTotals->handle($order);

        return $item->fresh();
    }
}
