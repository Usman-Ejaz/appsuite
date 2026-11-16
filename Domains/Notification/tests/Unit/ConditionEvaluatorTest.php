<?php

use Domains\Notification\Services\ConditionEvaluator;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->evaluator = new ConditionEvaluator;
    $this->context = [
        'order' => ['status' => 'Completed', 'total' => '750.00', 'notes' => null, 'item_names' => ['Blue Shirt', 'Tote']],
        'customer' => ['email' => 'John@Example.com'],
    ];
    $this->value = fn (string $token) => data_get($this->context, $token);
    $this->previous = ['order.status' => 'Processing'];
});

test('each operator compares a token with a value', function (string $field, string $operator, mixed $value, bool $expected) {
    $conditions = ['match' => 'All', 'rules' => [['field' => $field, 'operator' => $operator, 'value' => $value]]];

    expect($this->evaluator->passes($conditions, $this->value, $this->previous))->toBe($expected);
})->with([
    'eq' => ['order.status', 'Eq', 'Completed', true],
    'eq numeric strings' => ['order.total', 'Eq', 750, true],
    'neq' => ['order.status', 'Neq', 'Completed', false],
    'gt' => ['order.total', 'Gt', 500, true],
    'gte boundary' => ['order.total', 'Gte', '750', true],
    'lt' => ['order.total', 'Lt', 500, false],
    'lte' => ['order.total', 'Lte', 750, true],
    'gt on text never passes' => ['order.status', 'Gt', 1, false],
    'in' => ['order.status', 'In', ['Completed', 'Refunded'], true],
    'not in' => ['order.status', 'NotIn', ['Completed'], false],
    'contains text, case-insensitive' => ['customer.email', 'Contains', 'example.com', true],
    'contains list item' => ['order.item_names', 'Contains', 'Tote', true],
    'starts with' => ['customer.email', 'StartsWith', 'john@', true],
    'is empty' => ['order.notes', 'IsEmpty', null, true],
    'is not empty' => ['order.notes', 'IsNotEmpty', null, false],
    'changed to' => ['order.status', 'ChangedTo', 'Completed', true],
    'changed to, not changed' => ['order.status', 'ChangedTo', 'Processing', false],
    'changed from' => ['order.status', 'ChangedFrom', 'Processing', true],
    'unknown operator' => ['order.status', 'Matches', 'Completed', false],
]);

test('no rules always pass', function () {
    expect($this->evaluator->passes(null, $this->value))->toBeTrue()
        ->and($this->evaluator->passes(['match' => 'All', 'rules' => []], $this->value))->toBeTrue();
});

test('all needs every rule and any needs one', function () {
    $rules = [
        ['field' => 'order.status', 'operator' => 'Eq', 'value' => 'Completed'],
        ['field' => 'order.total', 'operator' => 'Gt', 'value' => 1000],
    ];

    expect($this->evaluator->passes(['match' => 'All', 'rules' => $rules], $this->value))->toBeFalse()
        ->and($this->evaluator->passes(['match' => 'Any', 'rules' => $rules], $this->value))->toBeTrue();
});
