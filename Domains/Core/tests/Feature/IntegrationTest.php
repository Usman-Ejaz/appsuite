<?php

use Domains\Core\Models\App;
use Domains\Core\Models\Integration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('an integration can be created with its fillable attributes', function () {
    $app = App::factory()->create();

    $integration = Integration::factory()->for($app)->create([
        'name' => 'Stripe',
        'provider' => 'stripe',
        'config' => ['api_key' => 'sk_test_123'],
    ]);

    expect($integration->name)->toBe('Stripe')
        ->and($integration->provider)->toBe('stripe')
        ->and($integration->config)->toBe(['api_key' => 'sk_test_123']);
});

test('an integration belongs to an app', function () {
    $app = App::factory()->create();
    $integration = Integration::factory()->for($app)->create();

    expect($integration->app)->toBeInstanceOf(App::class)
        ->and($integration->app->id)->toBe($app->id);
});
