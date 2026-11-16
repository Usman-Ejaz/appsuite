<?php

namespace Domains\Notification\Enums;

/**
 * How sending a trigger notification went.
 */
enum NotificationStatus: string
{
    /**
     * Handed to the channel successfully.
     */
    case SENT = 'Sent';

    /**
     * Every attempt failed; the error is logged.
     */
    case FAILED = 'Failed';
}
