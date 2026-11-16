<?php

namespace Domains\CMS\Database\Seeders;

use Domains\CMS\Enums\CMSTrigger;
use Domains\Notification\Database\Seeders\BaseTriggerSeeder;

class CMSTriggerSeeder extends BaseTriggerSeeder
{
    protected function triggers(): string
    {
        return CMSTrigger::class;
    }
}
