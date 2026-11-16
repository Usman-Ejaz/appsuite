<?php

namespace Domains\Ecommerce\Enums;

/**
 * The Ecommerce events companies can attach notification actions to.
 */
enum EcommerceTrigger: string
{
    /**
     * An order was placed.
     */
    case ORDER_CREATED = 'Ecommerce:Order:Created';

    /**
     * An order's status changed.
     */
    case ORDER_STATUS_CHANGED = 'Ecommerce:Order:StatusChanged';

    public function label(): string
    {
        return match ($this) {
            self::ORDER_CREATED => 'Order Created',
            self::ORDER_STATUS_CHANGED => 'Order Status Changed',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ORDER_CREATED => 'Fires when a new order is placed.',
            self::ORDER_STATUS_CHANGED => "Fires when an order's status changes, e.g. from Pending to Processing.",
        };
    }
}
