<?php

namespace Domains\Ecommerce\Database\Seeders;

use Domains\Core\Enums\AppCode;
use Domains\Ecommerce\Enums\EcommerceTrigger;
use Domains\Notification\Database\Seeders\BaseTriggerSeeder;

class EcommerceTriggerSeeder extends BaseTriggerSeeder
{
    protected function appCode(): AppCode
    {
        return AppCode::ECOMMERCE;
    }

    protected function data(): array
    {
        return [
            [
                'trigger' => EcommerceTrigger::ORDER_CREATED,
                'label' => 'Order Created',
                'description' => 'Fires when a new order is placed.',
            ],
            [
                'trigger' => EcommerceTrigger::ORDER_STATUS_CHANGED,
                'label' => 'Order Status Changed',
                'description' => "Fires when an order's status changes, e.g. from Pending to Processing.",
            ],
        ];
    }
}
