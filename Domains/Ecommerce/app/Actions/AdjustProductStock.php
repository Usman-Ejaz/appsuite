<?php

namespace Domains\Ecommerce\Actions;

use Domains\Ecommerce\Models\EcommerceProduct;

class AdjustProductStock
{
    /**
     * Signed stock adjustment — positive to restore, negative to consume.
     * Floors at zero rather than going negative. No-ops for products that
     * don't track inventory.
     */
    public function handle(EcommerceProduct $product, int $delta): void
    {
        if (! $product->track_inventory) {
            return;
        }

        $product->update([
            'stock_quantity' => max(0, $product->stock_quantity + $delta),
        ]);
    }
}
