<?php

use Domains\Identity\Models\ApiKey;
use Tests\TestCase;

uses(TestCase::class);

test('permissions passed as an array are checked, as the permission gate passes them', function () {
    $apiKey = new ApiKey(['abilities' => ['cms:blogs:view']]);

    expect($apiKey->can(['cms:blogs:view']))->toBeTrue()
        ->and($apiKey->can(['cms:blogs:delete']))->toBeFalse();
});

test('permissions passed variadically are checked', function () {
    $apiKey = new ApiKey(['abilities' => ['cms:blogs:view', 'cms:blogs:edit']]);

    expect($apiKey->can('cms:blogs:view', 'cms:blogs:edit'))->toBeTrue()
        ->and($apiKey->can('cms:blogs:view', 'cms:blogs:delete'))->toBeFalse();
});

test('the wildcard ability grants every permission', function () {
    $apiKey = new ApiKey(['abilities' => ['*']]);

    expect($apiKey->can(['cms:blogs:delete', 'ecommerce:orders:view']))->toBeTrue();
});

test('null or empty abilities grant nothing', function (?array $abilities) {
    $apiKey = new ApiKey(['abilities' => $abilities]);

    expect($apiKey->can(['cms:blogs:view']))->toBeFalse();
})->with([
    'null' => [null],
    'empty' => [[]],
]);
