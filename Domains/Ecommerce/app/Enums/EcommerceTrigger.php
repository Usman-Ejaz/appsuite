<?php

namespace Domains\Ecommerce\Enums;

/**
 * The Ecommerce events companies can attach notification actions to. Each value is an
 * `Ecommerce:<Module>:<Event>` code; its app, label and description live in
 * `EcommerceTriggerSeeder`.
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
}
