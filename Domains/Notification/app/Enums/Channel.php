<?php

namespace Domains\Notification\Enums;

/**
 * The channel a trigger action sends through. Each action has exactly one.
 */
enum Channel: string
{
    /**
     * Sent by email.
     */
    case EMAIL = 'Email';

    /**
     * Stored in the recipient's in-app inbox.
     */
    case DATABASE = 'Database';

    /**
     * Sent by text message.
     */
    case SMS = 'Sms';

    /**
     * Sent as a mobile or browser push notification.
     */
    case PUSH = 'Push';

    /**
     * Sent as an HTTP callback to a URL.
     */
    case WEBHOOK = 'Webhook';

    case FCM = 'FCM';
}
