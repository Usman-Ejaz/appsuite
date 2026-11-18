<?php

namespace Domains\Notification\Database\Seeders;

use Domains\Core\Enums\AppCode;
use Domains\Identity\Database\Seeders\BasePermissionSeeder;
use Domains\Notification\Enums\NotificationPermission;

class NotificationPermissionSeeder extends BasePermissionSeeder
{
    protected function appCode(): AppCode
    {
        return AppCode::SYSTEM;
    }

    protected function data(): array
    {
        return [
            [
                'permission' => NotificationPermission::TRIGGER_ACTION_VIEW,
                'label' => 'View Notification Actions',
                'description' => 'View the notification actions configured on each trigger.',
            ],
            [
                'permission' => NotificationPermission::TRIGGER_ACTION_CREATE,
                'label' => 'Create Notification Actions',
                'description' => 'Add notification actions, with their recipients and templates.',
            ],
            [
                'permission' => NotificationPermission::TRIGGER_ACTION_UPDATE,
                'label' => 'Update Notification Actions',
                'description' => 'Edit notification actions, including their conditions and templates.',
            ],
            [
                'permission' => NotificationPermission::TRIGGER_ACTION_DELETE,
                'label' => 'Delete Notification Actions',
                'description' => 'Remove notification actions.',
            ],
            [
                'permission' => NotificationPermission::LOG_VIEW,
                'label' => 'View Notification Logs',
                'description' => 'View the log of sent and failed notifications.',
            ],
        ];
    }
}
