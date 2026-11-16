<?php

use Domains\Ecommerce\Database\Seeders\EcommerceTriggerSeeder;
use Domains\Ecommerce\Enums\EcommerceTrigger;
use Domains\Notification\Models\Trigger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a domain trigger seeder seeds every case of its enum', function () {
    $this->seed(EcommerceTriggerSeeder::class);

    $trigger = Trigger::where('code', 'Ecommerce:Order:Created')->sole();

    expect(Trigger::count())->toBe(count(EcommerceTrigger::cases()))
        ->and($trigger->name)->toBe('Order Created')
        ->and($trigger->domain)->toBe('Ecommerce')
        ->and($trigger->module)->toBe('Order')
        ->and($trigger->app_code)->toBe('ecommerce');
});

test('re-seeding updates triggers in place', function () {
    $this->seed(EcommerceTriggerSeeder::class);
    $id = Trigger::where('code', 'Ecommerce:Order:Created')->value('id');

    $this->seed(EcommerceTriggerSeeder::class);

    expect(Trigger::count())->toBe(count(EcommerceTrigger::cases()))
        ->and(Trigger::where('code', 'Ecommerce:Order:Created')->value('id'))->toBe($id);
});
