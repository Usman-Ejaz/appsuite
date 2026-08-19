<?php

use Domains\Core\Models\App;
use Domains\Core\Models\Integration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('an app can be created with its fillable attributes', function () {
    $app = App::factory()->create([
        'name' => 'CRM',
        'code' => 'crm',
        'is_active' => true,
    ]);

    expect($app->name)->toBe('CRM')
        ->and($app->code)->toBe('crm')
        ->and($app->is_active)->toBeTrue();

    $this->assertDatabaseHas('apps', ['code' => 'crm']);
});

test('an app has many integrations', function () {
    $app = App::factory()->create();

    Integration::factory()->for($app)->count(3)->create();

    expect($app->integrations()->count())->toBe(3)
        ->and($app->integrations->first())->toBeInstanceOf(Integration::class);
});
