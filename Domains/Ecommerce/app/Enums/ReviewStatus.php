<?php

namespace Domains\Ecommerce\Enums;

/**
 * The moderation state of a customer review.
 */
enum ReviewStatus: string
{
    /**
     * Awaiting moderation before it's visible.
     */
    case PENDING = 'Pending';

    /**
     * Approved and visible.
     */
    case APPROVED = 'Approved';

    /**
     * Rejected and hidden.
     */
    case REJECTED = 'Rejected';
}
