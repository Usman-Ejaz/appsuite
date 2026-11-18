<?php

use Domains\CMS\Enums\CMSTrigger;
use Domains\CMS\Models\FormSubmission;
use Domains\Ecommerce\Actions\CancelOrder;
use Domains\Ecommerce\Enums\EcommerceTrigger;
use Domains\Ecommerce\Enums\OrderStatus;
use Domains\Ecommerce\Models\Order;
use Domains\Ecommerce\Repositories\OrderRepository;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Customer;
use Domains\Notification\Actions\TriggerFormSubmissionAction;
use Domains\Notification\Actions\TriggerOrderAction;
use Domains\Notification\Services\Triggers\TriggerDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->order = Order::factory()->create(['company_id' => $this->company->id]);

    Queue::fake();
});

test('a trigger without a chained queue runs on the notification queue', function () {
    TriggerDispatcher::dispatch(EcommerceTrigger::ORDER_CREATED)->for($this->order);

    Queue::assertPushedOn(config('notification.queue'), TriggerOrderAction::class, fn (TriggerOrderAction $action) => $action->trigger === EcommerceTrigger::ORDER_CREATED
        && $action->resource->is($this->order));
});

test('a chained queue overrides the default one', function () {
    TriggerDispatcher::dispatch(EcommerceTrigger::ORDER_CREATED)->for($this->order)->onQueue('notifications-high');

    Queue::assertPushedOn('notifications-high', TriggerOrderAction::class);
});

test('meta passed with with() reaches the trigger action', function () {
    TriggerDispatcher::dispatch(EcommerceTrigger::ORDER_STATUS_CHANGED)
        ->for($this->order)
        ->with(['previous' => ['order.status' => 'Pending']]);

    Queue::assertPushed(TriggerOrderAction::class, fn (TriggerOrderAction $action) => $action->meta === ['previous' => ['order.status' => 'Pending']]);
});

test('a form submission runs the form submission trigger action', function () {
    TriggerDispatcher::dispatch(CMSTrigger::FORM_SUBMITTED)->for(new FormSubmission);

    Queue::assertPushed(TriggerFormSubmissionAction::class);
});

test('a trigger dispatched without a record fails loudly', function () {
    expect(function () {
        TriggerDispatcher::dispatch(EcommerceTrigger::ORDER_CREATED);
    })->toThrow(LogicException::class);
});

test('a record no trigger action handles is rejected', function () {
    expect(function () {
        TriggerDispatcher::dispatch(EcommerceTrigger::ORDER_CREATED)->for(new Customer);
    })->toThrow(InvalidArgumentException::class);
});

test('saving an order directly fires no trigger', function () {
    Order::factory()->create(['company_id' => $this->company->id]);
    $this->order->update(['status' => OrderStatus::PROCESSING]);

    Queue::assertNothingPushed();
});

test('the order repository fires a status change only when the status moves', function () {
    $orders = app(OrderRepository::class);

    $orders->update($this->order->id, ['currency' => 'EUR']);
    Queue::assertNothingPushed();

    $orders->update($this->order->id, ['status' => OrderStatus::PROCESSING]);
    Queue::assertPushed(TriggerOrderAction::class, fn (TriggerOrderAction $action) => $action->trigger === EcommerceTrigger::ORDER_STATUS_CHANGED
        && $action->meta === ['previous' => ['order.status' => 'Pending']]);
});

test('cancelling an order fires a status change from its previous status', function () {
    app(CancelOrder::class)->handle($this->order);

    Queue::assertPushed(TriggerOrderAction::class, fn (TriggerOrderAction $action) => $action->trigger === EcommerceTrigger::ORDER_STATUS_CHANGED
        && $action->meta === ['previous' => ['order.status' => 'Pending']]);
});
