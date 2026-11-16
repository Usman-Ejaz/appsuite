<?php

namespace Domains\Notification\Enums;

enum NotificationPermission: string
{
    case TRIGGER_ACTION_VIEW = 'system:trigger-actions:view';
    case TRIGGER_ACTION_CREATE = 'system:trigger-actions:create';
    case TRIGGER_ACTION_UPDATE = 'system:trigger-actions:update';
    case TRIGGER_ACTION_DELETE = 'system:trigger-actions:delete';

    case LOG_VIEW = 'system:notification-logs:view';

    public function label(): string
    {
        return match ($this) {
            self::TRIGGER_ACTION_VIEW => 'View Notification Actions',
            self::TRIGGER_ACTION_CREATE => 'Create Notification Actions',
            self::TRIGGER_ACTION_UPDATE => 'Update Notification Actions',
            self::TRIGGER_ACTION_DELETE => 'Delete Notification Actions',

            self::LOG_VIEW => 'View Notification Logs',
        };
    }

    public function code(): string
    {
        return match ($this) {
            self::TRIGGER_ACTION_VIEW => 'system_trigger_actions_view',
            self::TRIGGER_ACTION_CREATE => 'system_trigger_actions_create',
            self::TRIGGER_ACTION_UPDATE => 'system_trigger_actions_update',
            self::TRIGGER_ACTION_DELETE => 'system_trigger_actions_delete',

            self::LOG_VIEW => 'system_notification_logs_view',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::TRIGGER_ACTION_VIEW => 'View the notification actions configured on each trigger.',
            self::TRIGGER_ACTION_CREATE => 'Add notification actions, with their recipients and templates.',
            self::TRIGGER_ACTION_UPDATE => 'Edit notification actions, including their conditions and templates.',
            self::TRIGGER_ACTION_DELETE => 'Remove notification actions.',

            self::LOG_VIEW => 'View the log of sent and failed notifications.',
        };
    }
}
