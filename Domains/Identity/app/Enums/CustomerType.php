<?php

namespace Domains\Identity\Enums;

/**
 * Whether a customer record represents a person or a business.
 */
enum CustomerType: string
{
    /**
     * A single person.
     */
    case INDIVIDUAL = 'Individual';

    /**
     * A company or organization. `business_name` is typically set for this type.
     */
    case BUSINESS = 'Business';
}
