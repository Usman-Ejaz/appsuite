<?php

namespace Domains\Ecommerce\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Ecommerce\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * @mixin OrderItem
 */
class OrderItemResource extends BaseResource
{
    public string $routeName = 'api.ecommerce.orders.items';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'order_id' => $this->order_id,
            'ecommerce_product_id' => $this->ecommerce_product_id,

            /**
             * The product's name at the time it was added to the order. This won't change even if the product is later renamed or removed.
             */
            'product_name' => $this->product_name,

            /**
             * The product's SKU at the time it was added to the order. This won't change even if the product's SKU is later updated.
             */
            'product_sku' => $this->product_sku,

            /**
             * The unit price charged at the time this item was added, independent of the product's current price.
             *
             * @example 24.99
             */
            'unit_price' => $this->unit_price,

            'quantity' => $this->quantity,

            /**
             * This line item's total: `unit_price` multiplied by `quantity`.
             *
             * @example 49.98
             */
            'subtotal' => $this->subtotal,
        ]);
    }

    /**
     * BaseResource::getLinks() assumes a single `{id}` route parameter,
     * which doesn't fit this resource's `/orders/{order}/items/{id}`
     * nesting — override to pass both route parameters.
     */
    public function getLinks(): ?array
    {
        if (empty($this->routeName) || empty($this->id) || empty($this->order_id)) {
            return null;
        }

        return [
            'self' => Route::has("{$this->routeName}.get")
                ? route("{$this->routeName}.get", ['order' => $this->order_id, 'id' => $this->id])
                : null,
            'parent' => Route::has("{$this->routeName}.list")
                ? route("{$this->routeName}.list", ['order' => $this->order_id])
                : null,
        ];
    }
}
