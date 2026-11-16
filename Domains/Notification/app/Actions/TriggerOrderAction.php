<?php

namespace Domains\Notification\Actions;

use Domains\Ecommerce\Models\Order;
use Domains\Notification\Services\Tokens\CustomerTokenResolver;
use Domains\Notification\Services\Tokens\OrderTokenResolver;
use Illuminate\Support\Collection;

/**
 * Runs the trigger actions for an order.
 *
 * @property Order $resource
 */
class TriggerOrderAction extends AbstractTriggerAction
{
    protected function tokenResolvers(): array
    {
        $this->resource->loadMissing(['customer', 'items']);

        return [
            new OrderTokenResolver($this->resource, $this->meta['previous'] ?? []),
            new CustomerTokenResolver($this->resource->customer),
        ];
    }

    public function dynamicRecipients(string $code): Collection
    {
        return collect($code === 'customer' ? [$this->resource->customer] : [])->filter();
    }
}
