<?php

use Domains\CMS\Database\Seeders\CMSTriggerSeeder;
use Domains\CMS\Enums\CMSTrigger;
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

test('the CMS trigger seeder seeds every CMS trigger under the cms app', function () {
    $this->seed(CMSTriggerSeeder::class);

    $trigger = Trigger::where('code', 'CMS:FormSubmission:StatusChanged')->sole();

    expect(Trigger::count())->toBe(count(CMSTrigger::cases()))
        ->and(Trigger::where('app_code', 'cms')->count())->toBe(count(CMSTrigger::cases()))
        ->and($trigger->name)->toBe('Form Submission Status Changed')
        ->and($trigger->description)->toBe("Fires when a form submission's status changes, e.g. from New to Read.")
        ->and($trigger->domain)->toBe('CMS')
        ->and($trigger->module)->toBe('FormSubmission');
});
