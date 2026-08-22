<?php

namespace Domains\Ecommerce\Enums;

/**
 * How a coupon's discount amount is calculated.
 */
enum CouponType: string
{
    /**
     * A flat currency amount is deducted from the order subtotal.
     */
    case FIXED = 'Fixed';

    /**
     * A percentage of the order subtotal is deducted, capped by `max_discount_amount`
     * when it's set.
     */
    case PERCENTAGE = 'Percentage';
}
