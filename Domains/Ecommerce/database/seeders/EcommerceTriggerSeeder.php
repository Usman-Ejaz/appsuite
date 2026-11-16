<?php

namespace Domains\Ecommerce\Database\Seeders;

use Domains\Ecommerce\Enums\EcommerceTrigger;
use Domains\Notification\Database\Seeders\BaseTriggerSeeder;

class EcommerceTriggerSeeder extends BaseTriggerSeeder
{
    protected function triggers(): string
    {
        return EcommerceTrigger::class;
    }
}
