<?php

use Domains\Ecommerce\Models\Order;
use Domains\Identity\Models\Customer;
use Domains\Notification\Services\Tokens\CustomerTokenResolver;
use Domains\Notification\Services\Tokens\OrderTokenResolver;
use Domains\Notification\Services\Tokens\TokenParser;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->parser = new TokenParser;

    $order = (new Order)->forceFill([
        'order_number' => 'ORD-1',
        'total' => '1234.5',
        'currency' => 'EUR',
        'notes' => null,
        'created_at' => '2026-10-04 09:30:00',
    ]);
    $order->setRelation('items', collect());

    $this->resolvers = TokenParser::keyed([
        new OrderTokenResolver($order, ['order.status' => 'Pending']),
        new CustomerTokenResolver((new Customer)->forceFill(['name' => 'john doe'])),
    ]);
});

test('tokens are replaced and the rest of the template is kept', function () {
    expect($this->parser->parse('Order {{ order.order_number }} for {{customer.name}}.', $this->resolvers))
        ->toBe('Order ORD-1 for john doe.');
});

test('filters format values', function (string $template, string $expected) {
    expect($this->parser->parse($template, $this->resolvers))->toBe($expected);
})->with([
    'upper' => ['{{ customer.name | upper }}', 'JOHN DOE'],
    'title' => ['{{ customer.name | title }}', 'John Doe'],
    'chained' => ['{{ customer.name | title | lower }}', 'john doe'],
    'money in the order\'s own currency' => ['{{ order.total | money }}', '€1,234.50'],
    'money in a given currency' => ["{{ order.total | money:'USD' }}", '$1,234.50'],
    'number' => ['{{ order.total | number:1 }}', '1,234.5'],
    'date' => ["{{ order.created_at | date:'d/m/Y' }}", '04/10/2026'],
    'default' => ["{{ order.notes | default:'No notes' }}", 'No notes'],
    'an unknown filter leaves the value' => ['{{ customer.name | shout }}', 'john doe'],
]);

test('computed tokens come from the resolver', function () {
    expect($this->parser->parse('{{ order.previous_status }} ({{ order.item_count }} items)', $this->resolvers))
        ->toBe('Pending (0 items)');
});

test('unknown tokens and unknown prefixes render empty', function () {
    expect($this->parser->parse('[{{ customer.password }}][{{ invoice.total }}][{{ order }}]', $this->resolvers))->toBe('[][][]');
});

test('values are escaped in html templates only', function () {
    $resolvers = TokenParser::keyed([new CustomerTokenResolver((new Customer)->forceFill(['name' => '<script>alert(1)</script>']))]);

    expect($this->parser->parse('<p>{{ customer.name }}</p>', $resolvers, html: true))
        ->toBe('<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>')
        ->and($this->parser->parse('{{ customer.name }}', $resolvers))
        ->toBe('<script>alert(1)</script>');
});
