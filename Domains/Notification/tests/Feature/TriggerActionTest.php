<?php

use Domains\CMS\Actions\SubmitForm;
use Domains\CMS\Database\Factories\FormFactory;
use Domains\CMS\Database\Seeders\CMSTriggerSeeder;
use Domains\CMS\Enums\CMSTrigger;
use Domains\Ecommerce\Database\Seeders\EcommerceTriggerSeeder;
use Domains\Ecommerce\Enums\EcommerceTrigger;
use Domains\Ecommerce\Enums\OrderStatus;
use Domains\Ecommerce\Models\Order;
use Domains\Ecommerce\Repositories\OrderRepository;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Customer;
use Domains\Identity\Models\Role;
use Domains\Identity\Models\User;
use Domains\Notification\Enums\Channel;
use Domains\Notification\Enums\NotificationStatus;
use Domains\Notification\Enums\RecipientType;
use Domains\Notification\Models\NotificationLog;
use Domains\Notification\Models\Trigger;
use Domains\Notification\Models\TriggerAction;
use Domains\Notification\Notifications\TriggerNotification;
use Domains\Notification\Services\Triggers\TriggerDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed([EcommerceTriggerSeeder::class, CMSTriggerSeeder::class]);

    $this->company = Company::factory()->create(['name' => 'Acme Ltd']);
    $this->owner = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    $this->customer = Customer::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

/**
 * Create an active trigger action with the given recipient rows.
 *
 * @param  array<string, mixed>  $attributes
 * @param  list<array<string, mixed>>  $recipients
 */
function createTriggerAction(Company $company, BackedEnum $trigger, array $attributes, array $recipients): TriggerAction
{
    $action = TriggerAction::factory()->create([
        'company_id' => $company->id,
        'trigger_id' => Trigger::where('code', $trigger->value)->value('id'),
        ...$attributes,
    ]);

    foreach ($recipients as $recipient) {
        $action->recipients()->create($recipient);
    }

    return $action;
}

/**
 * Place an order through the repository, which fires `ORDER_CREATED` like a real checkout.
 *
 * @param  array<string, mixed>  $attributes
 */
function createOrder(Company $company, Customer $customer, array $attributes = []): Order
{
    $attributes = Order::factory()->raw(['company_id' => $company->id, 'customer_id' => $customer->id, ...$attributes]);

    // company_id normally comes from the signed-in user; there is none here.
    return Order::unguarded(fn () => app(OrderRepository::class)->create($attributes));
}

test('an order confirmation is sent to the customer with its tokens filled in', function () {
    Notification::fake();

    createTriggerAction($this->company, EcommerceTrigger::ORDER_CREATED, [
        'subject' => 'Order {{ order.order_number }} confirmed',
        'body' => '<p>Hi {{ customer.name }}, your total is {{ order.total | money }}. — {{ company.name }}</p>',
    ], [['type' => RecipientType::DYNAMIC, 'code' => 'customer']]);

    $order = createOrder($this->company, $this->customer, ['total' => 99.5]);

    Notification::assertSentTo($this->customer, TriggerNotification::class, function (TriggerNotification $notification, array $channels) use ($order) {
        return $channels === ['mail']
            && $notification->subject === "Order {$order->order_number} confirmed"
            && $notification->body === '<p>Hi John Doe, your total is $99.50. — Acme Ltd</p>';
    });
});

test('the owner gets an in-app notification and it is logged', function () {
    createTriggerAction($this->company, EcommerceTrigger::ORDER_CREATED, [
        'channel' => Channel::DATABASE,
        'subject' => 'New order {{ order.order_number }}',
        'body' => '{{ customer.name }} placed an order.',
    ], [['type' => RecipientType::OWNER]]);

    $order = createOrder($this->company, $this->customer);

    expect($this->owner->notifications()->sole()->data)->toMatchArray([
        'title' => "New order {$order->order_number}",
        'body' => 'John Doe placed an order.',
    ]);

    $log = NotificationLog::withoutGlobalScopes()->sole();

    expect($log->status)->toBe(NotificationStatus::SENT)
        ->and($log->channel)->toBe(Channel::DATABASE)
        ->and($log->notifiable_id)->toBe($this->owner->id)
        ->and($log->resource_id)->toBe($order->id);
});

test("a trigger's notifications are queued on the trigger's queue", function () {
    Notification::fake();

    createTriggerAction($this->company, EcommerceTrigger::ORDER_CREATED, [], [['type' => RecipientType::DYNAMIC, 'code' => 'customer']]);
    $order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    TriggerDispatcher::dispatch(EcommerceTrigger::ORDER_CREATED)->for($order)->onQueue('notifications-high');

    Notification::assertSentTo($this->customer, TriggerNotification::class, fn (TriggerNotification $notification) => $notification->queue === 'notifications-high');
});

test('an email to a fixed address is sent and logged with that address', function () {
    createTriggerAction($this->company, EcommerceTrigger::ORDER_CREATED, [
        'subject' => 'New order',
        'body' => "A new order came in.\nTotal: {{ order.total | money }}",
    ], [['type' => RecipientType::STATIC, 'address' => 'ops@example.com']]);

    createOrder($this->company, $this->customer, ['total' => 10]);

    $log = NotificationLog::withoutGlobalScopes()->sole();

    expect($log->status)->toBe(NotificationStatus::SENT)
        ->and($log->address)->toBe('ops@example.com')
        ->and($log->data['subject'])->toBe('New order');
});

test('a form submission is emailed to its submitter with their answers escaped', function () {
    Notification::fake();

    createTriggerAction($this->company, CMSTrigger::FORM_SUBMITTED, [
        'subject' => 'We got your message',
        'body' => '<p>Thanks {{ submission.data.name }}, we will reply about {{ form.name }}.</p>',
    ], [['type' => RecipientType::DYNAMIC, 'code' => 'submitter']]);

    $form = FormFactory::new()->create(['company_id' => $this->company->id, 'name' => 'Contact Us']);

    // Submissions come in through an authenticated company actor, which stamps the company.
    $this->actingAs($this->owner);

    app(SubmitForm::class)->handle($form, ['name' => '<script>alert(1)</script>', 'email' => 'visitor@example.com'], null, null);

    Notification::assertSentOnDemand(TriggerNotification::class, function (TriggerNotification $notification, array $channels, AnonymousNotifiable $notifiable) {
        return $notifiable->routes['mail'] === 'visitor@example.com'
            && $notification->body === '<p>Thanks &lt;script&gt;alert(1)&lt;/script&gt;, we will reply about Contact Us.</p>';
    });
});

test('a status change only notifies actions whose conditions pass', function () {
    Notification::fake();

    createTriggerAction($this->company, EcommerceTrigger::ORDER_STATUS_CHANGED, [
        'subject' => 'Order moved from {{ order.previous_status }} to {{ order.status }}',
        'conditions' => ['match' => 'All', 'rules' => [['field' => 'order.status', 'operator' => 'ChangedTo', 'value' => 'Completed']]],
    ], [['type' => RecipientType::DYNAMIC, 'code' => 'customer']]);

    $order = createOrder($this->company, $this->customer);

    app(OrderRepository::class)->update($order->id, ['status' => OrderStatus::PROCESSING]);
    Notification::assertNothingSent();

    app(OrderRepository::class)->update($order->id, ['status' => OrderStatus::COMPLETED]);
    Notification::assertSentTo($this->customer, TriggerNotification::class, fn (TriggerNotification $notification) => $notification->subject === 'Order moved from Processing to Completed');
    Notification::assertSentTimes(TriggerNotification::class, 1);
});

test('only the actions of the company the order belongs to run', function () {
    Notification::fake();

    $otherCompany = Company::factory()->create();
    $otherOwner = User::factory()->create(['company_id' => $otherCompany->id, 'is_owner' => true]);

    createTriggerAction($this->company, EcommerceTrigger::ORDER_CREATED, ['channel' => Channel::DATABASE], [['type' => RecipientType::OWNER]]);
    createTriggerAction($otherCompany, EcommerceTrigger::ORDER_CREATED, ['channel' => Channel::DATABASE], [['type' => RecipientType::OWNER]]);

    createOrder($this->company, $this->customer);

    Notification::assertSentTo($this->owner, TriggerNotification::class);
    Notification::assertNotSentTo($otherOwner, TriggerNotification::class);
});

test('a user or role from another company is never notified', function () {
    Notification::fake();

    $otherCompany = Company::factory()->create();
    $outsider = User::factory()->create(['company_id' => $otherCompany->id]);
    $outsiderRole = Role::create(['company_id' => $otherCompany->id, 'name' => 'Sales', 'guard_name' => 'web']);
    $outsider->assignRole($outsiderRole);

    createTriggerAction($this->company, EcommerceTrigger::ORDER_CREATED, ['channel' => Channel::DATABASE], [
        ['type' => RecipientType::USER, 'recipient_type' => $outsider->getMorphClass(), 'recipient_id' => $outsider->id],
        ['type' => RecipientType::ROLE, 'recipient_type' => $outsiderRole->getMorphClass(), 'recipient_id' => $outsiderRole->id],
    ]);

    createOrder($this->company, $this->customer);

    Notification::assertNothingSent();
});

test('a role recipient reaches every user holding the role', function () {
    Notification::fake();

    $role = Role::create(['company_id' => $this->company->id, 'name' => 'Fulfilment', 'guard_name' => 'web']);
    $packers = User::factory()->count(2)->create(['company_id' => $this->company->id]);
    $packers->each->assignRole($role);

    createTriggerAction($this->company, EcommerceTrigger::ORDER_CREATED, ['channel' => Channel::DATABASE], [
        ['type' => RecipientType::ROLE, 'recipient_type' => $role->getMorphClass(), 'recipient_id' => $role->id],
    ]);

    createOrder($this->company, $this->customer);

    Notification::assertSentTo($packers, TriggerNotification::class);
    Notification::assertNotSentTo($this->owner, TriggerNotification::class);
});

test('inactive actions do not send', function () {
    Notification::fake();

    createTriggerAction($this->company, EcommerceTrigger::ORDER_CREATED, ['is_active' => false], [['type' => RecipientType::OWNER]]);

    createOrder($this->company, $this->customer);

    Notification::assertNothingSent();
});

test('a send that keeps failing is logged as failed', function () {
    Event::listen(NotificationSending::class, fn () => throw new RuntimeException('SMTP down'));

    createTriggerAction($this->company, EcommerceTrigger::ORDER_CREATED, ['body' => 'Hi'], [['type' => RecipientType::DYNAMIC, 'code' => 'customer']]);

    // The sync test queue rethrows the failure into the request; a real worker would not.
    expect(fn () => createOrder($this->company, $this->customer))->toThrow(RuntimeException::class, 'SMTP down');

    $log = NotificationLog::withoutGlobalScopes()->sole();

    expect($log->status)->toBe(NotificationStatus::FAILED)
        ->and($log->error)->toBe('SMTP down');
});
