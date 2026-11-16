<?php

use Domains\Ecommerce\Models\Order;
use Domains\Identity\Models\ApiKey;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Customer;
use Domains\Identity\Models\User;
use Domains\Notification\Services\Tokens\ActorTokenResolver;
use Domains\Notification\Services\Tokens\OrderTokenResolver;
use Domains\Notification\Services\Tokens\RecipientTokenResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('every visible column is a token without any code for it', function () {
    $company = Company::factory()->create();
    $order = Order::factory()->create([
        'company_id' => $company->id,
        'customer_id' => Customer::factory()->create(['company_id' => $company->id])->id,
        'notes' => 'Leave at the door',
    ]);

    $resolver = new OrderTokenResolver($order);

    expect($resolver->get('notes'))->toBe('Leave at the door')
        ->and($resolver->get('order_number'))->toBe($order->order_number)
        ->and($resolver->get('status'))->toBe('Pending')
        ->and($resolver->get('company_id'))->toBeNull();
});

test('the token list is built from the table, plus computed tokens, minus excluded ones', function () {
    $tokens = (new OrderTokenResolver(new Order))->tokens();

    expect($tokens)->toContain('order.order_number', 'order.total', 'order.notes', 'order.previous_status', 'order.item_names', 'order.item_count')
        ->not->toContain('order.company_id', 'order.creator_id');
});

test('hidden attributes are never tokens', function () {
    $user = User::factory()->create(['name' => 'Jane']);

    $resolver = new RecipientTokenResolver($user);

    expect($resolver->get('name'))->toBe('Jane')
        ->and($resolver->get('password'))->toBeNull()
        ->and($resolver->get('remember_token'))->toBeNull()
        ->and($resolver->tokens())->toBe(['recipient.name', 'recipient.email', 'recipient.phone']);
});

test('an api key actor only exposes its name, never its key', function () {
    $apiKey = ApiKey::factory()->create(['name' => 'Storefront']);

    $resolver = new ActorTokenResolver($apiKey);

    expect($resolver->get('name'))->toBe('Storefront')
        ->and($resolver->get('api_key'))->toBeNull()
        ->and($resolver->get('api_secret'))->toBeNull();
});

test('a bare address recipient exposes itself as email or phone', function () {
    $email = new RecipientTokenResolver(Notification::route('mail', 'ops@example.com'));
    $phone = new RecipientTokenResolver(Notification::route('sms', '+15550100'));

    expect($email->get('email'))->toBe('ops@example.com')
        ->and($email->get('phone'))->toBeNull()
        ->and($phone->get('phone'))->toBe('+15550100');
});
