<?php

use Domains\CMS\Models\FormSubmission;
use Domains\Ecommerce\Models\Order;
use Domains\Ecommerce\Models\OrderItem;
use Domains\Identity\Models\Customer;
use Domains\Notification\Services\Tokens\CustomerTokenResolver;
use Domains\Notification\Services\Tokens\FormSubmissionTokenResolver;
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

test('text, number, date, list and boolean filters format values', function (string $template, string $expected) {
    $this->travelTo('2026-10-04 11:30:00');

    $resolvers = TokenParser::keyed([new FormSubmissionTokenResolver((new FormSubmission)->forceFill([
        'data' => [
            'name' => 'jane doe',
            'message' => '  Please call me back about the bulk order tomorrow  ',
            'rating' => 87.26,
            'budget' => 1260000,
            'callback_at' => '2026-10-04 14:05:00',
            'topics' => ['Pricing', 'Delivery', 'Returns'],
            'subscribe' => true,
            'urgent' => '0',
        ],
        'created_at' => '2026-10-04 09:30:00',
    ]))]);

    expect($this->parser->parse($template, $resolvers))->toBe($expected);
})->with([
    'capitalize' => ['{{ submission.data.name | capitalize }}', 'Jane doe'],
    'trim' => ['[{{ submission.data.message | trim }}]', '[Please call me back about the bulk order tomorrow]'],
    'truncate' => ['{{ submission.data.message | trim | truncate:20 }}', 'Please call me back...'],
    'words' => ['{{ submission.data.message | words:3 }}', '  Please call me...'],
    'percent with up to two decimals' => ['{{ submission.data.rating | percent }}', '87.26%'],
    'percent with fixed decimals' => ['{{ submission.data.rating | percent:1 }}', '87.3%'],
    'abbreviate' => ['{{ submission.data.budget | abbreviate }}', '1.3M'],
    'datetime' => ['{{ submission.data.callback_at | datetime }}', '04 Oct 2026 14:05'],
    'time' => ["{{ submission.data.callback_at | time:'g:i A' }}", '2:05 PM'],
    'ago' => ['{{ submission.created_at | ago }}', '2 hours ago'],
    'count' => ['{{ submission.data.topics | count }}', '3'],
    'first' => ['{{ submission.data.topics | first }}', 'Pricing'],
    'last' => ['{{ submission.data.topics | last | upper }}', 'RETURNS'],
    'yesno' => ['{{ submission.data.subscribe | yesno }}', 'Yes'],
    'yesno with labels' => ["{{ submission.data.urgent | yesno:'Urgent, Normal' }}", 'Normal'],
    'yesno leaves non-booleans' => ["{{ submission.data.name | yesno:'Y,N' }}", 'jane doe'],
    'list filters leave non-lists' => ['{{ submission.data.name | first }}', 'jane doe'],
    'date filters leave non-dates' => ['{{ submission.data.name | ago }}', 'jane doe'],
]);

describe('table', function () {
    beforeEach(function () {
        $order = (new Order)->forceFill(['currency' => 'EUR']);
        $order->setRelation('items', collect([
            new OrderItem(['product_name' => 'Mug', 'quantity' => 2, 'unit_price' => '5', 'subtotal' => '10']),
            new OrderItem(['product_name' => '<b>Tee</b>', 'quantity' => 1, 'unit_price' => '20', 'subtotal' => '20']),
        ]));

        $this->tableResolvers = TokenParser::keyed([
            new OrderTokenResolver($order),
            new FormSubmissionTokenResolver((new FormSubmission)->forceFill(['data' => ['full_name' => 'Jane', 'topics' => ['Pricing', 'Delivery']]])),
        ]);
    });

    test('rows render one line each, under a header, in plain text', function () {
        expect($this->parser->parse('{{ order.items | table }}', $this->tableResolvers))
            ->toBe("Product | Quantity | Price | Total\nMug | 2 | €5.00 | €10.00\n<b>Tee</b> | 1 | €20.00 | €20.00");
    });

    test('the argument picks the columns', function () {
        expect($this->parser->parse("{{ order.items | table:'product, total' }}", $this->tableResolvers))
            ->toBe("Product | Total\nMug | €10.00\n<b>Tee</b> | €20.00");
    });

    test('rows render as an html table with every cell escaped', function () {
        $html = $this->parser->parse("<p>Your order:</p>{{ order.items | table:'product,quantity' }}", $this->tableResolvers, html: true);

        expect($html)
            ->toStartWith('<p>Your order:</p><table')
            ->toContain('>Product</th>', '>Quantity</th>', '>Mug</td>', '>2</td>', '>&lt;b&gt;Tee&lt;/b&gt;</td>')
            ->not->toContain('<b>Tee</b>', 'Price')
            ->and(substr_count($html, '<tr>'))->toBe(3);
    });

    test('a key/value array renders one labelled row per key', function () {
        expect($this->parser->parse('{{ submission.data | table }}', $this->tableResolvers))
            ->toBe("Full Name: Jane\nTopics: Pricing, Delivery")
            ->and($this->parser->parse('{{ submission.data | table }}', $this->tableResolvers, html: true))
            ->toContain('>Full Name</td>', '>Jane</td>', '>Pricing, Delivery</td>')
            ->not->toContain('<thead>');
    });

    test('a plain list renders one row per item', function () {
        expect($this->parser->parse('{{ submission.data.topics | table }}', $this->tableResolvers))
            ->toBe("Pricing\nDelivery");
    });

    test('a value that is not a list is left unchanged and still escaped', function () {
        $resolvers = TokenParser::keyed([new CustomerTokenResolver((new Customer)->forceFill(['name' => '<i>x</i>']))]);

        expect($this->parser->parse('{{ customer.name | table }}', $resolvers, html: true))->toBe('&lt;i&gt;x&lt;/i&gt;');
    });
});

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
