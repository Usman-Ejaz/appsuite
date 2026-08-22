<?php

namespace Domains\Ecommerce\Enums;

/**
 * The lifecycle state of an order.
 */
enum OrderStatus: string
{
    /**
     * The order has been placed but processing hasn't started yet.
     */
    case PENDING = 'Pending';

    /**
     * The order is being processed for fulfillment.
     */
    case PROCESSING = 'Processing';

    /**
     * The order has been fulfilled.
     */
    case COMPLETED = 'Completed';

    /**
     * The order was cancelled before completion. Cancelling restocks its items and reverses any applied coupon's usage.
     */
    case CANCELLED = 'Cancelled';

    /**
     * The order was completed and has since been refunded.
     */
    case REFUNDED = 'Refunded';
}
