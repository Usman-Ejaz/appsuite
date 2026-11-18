<?php

namespace Domains\Notification\Enums;

/**
 * The Notification permissions a role can be granted. Each value is the permission's `name`, in the
 * `system:<resource>:<action>` shape the authorization gate checks; its label and description live in
 * `NotificationPermissionSeeder`.
 */
enum NotificationPermission: string
{
    case TRIGGER_ACTION_VIEW = 'system:trigger-actions:view';
    case TRIGGER_ACTION_CREATE = 'system:trigger-actions:create';
    case TRIGGER_ACTION_UPDATE = 'system:trigger-actions:update';
    case TRIGGER_ACTION_DELETE = 'system:trigger-actions:delete';

    case LOG_VIEW = 'system:notification-logs:view';
}
