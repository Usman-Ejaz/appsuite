<?php

namespace Domains\Notification\Enums;

/**
 * How a trigger action recipient is chosen.
 */
enum RecipientType: string
{
    /**
     * One specific user.
     */
    case USER = 'User';

    /**
     * Every user holding one of the company's roles.
     */
    case ROLE = 'Role';

    /**
     * The company's owner.
     */
    case OWNER = 'Owner';

    /**
     * Someone tied to the record that fired the trigger, e.g. an order's `customer`.
     */
    case DYNAMIC = 'Dynamic';

    /**
     * A fixed email address or phone number.
     */
    case STATIC = 'Static';
}
