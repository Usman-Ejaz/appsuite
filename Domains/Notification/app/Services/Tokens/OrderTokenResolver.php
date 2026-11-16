<?php

namespace Domains\Notification\Services\Tokens;

use Domains\Ecommerce\Models\Order;
use Domains\Ecommerce\Models\OrderItem;

/**
 * `{{ order.* }}` tokens: every visible order column, plus the computed ones below.
 *
 * @property Order|null $model
 */
class OrderTokenResolver extends AbstractTokenResolver
{
    /**
     * @param  array<string, mixed>  $previous  Values before the change, keyed by token, e.g. `order.status`.
     */
    public function __construct(?Order $order = null, protected array $previous = [])
    {
        parent::__construct($order);
    }

    public function prefix(): string
    {
        return 'order';
    }

    /**
     * `{{ order.previous_status }}`: the status before a status change.
     */
    public function getPreviousStatus(): ?string
    {
        return $this->previous['order.status'] ?? null;
    }

    /**
     * `{{ order.item_names }}`: the names of the ordered products.
     *
     * @return list<string>
     */
    public function getItemNames(): array
    {
        return $this->model?->items->map(fn (OrderItem $item) => $item->product_name)->values()->all() ?? [];
    }

    /**
     * `{{ order.item_count }}`: how many units were ordered.
     */
    public function getItemCount(): int
    {
        return (int) $this->model?->items->sum('quantity');
    }
}
