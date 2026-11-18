<?php

use Domains\Core\Models\App;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Identity\Models\ApiKey;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->app_ = App::factory()->create(['code' => 'ecommerce']);
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => false]);

    Permission::create([
        'app_id' => $this->app_->id,
        'name' => EcommercePermission::PRODUCT_VIEW->value,
        'guard_name' => 'web',
    ]);

    $this->user->givePermissionTo(EcommercePermission::PRODUCT_VIEW->value);
});

test('permission gate allows a plain permission string', function () {
    expect(Gate::forUser($this->user)->allows('permission', EcommercePermission::PRODUCT_VIEW->value))->toBeTrue();
});

test('permission gate allows an array of permission strings', function () {
    expect(Gate::forUser($this->user)->allows('permission', [EcommercePermission::PRODUCT_VIEW->value]))->toBeTrue();
});

test('permission gate allows a single enum case', function () {
    expect(Gate::forUser($this->user)->allows('permission', EcommercePermission::PRODUCT_VIEW))->toBeTrue();
});

test('permission gate allows an array of enum cases', function () {
    expect(Gate::forUser($this->user)->allows('permission', [EcommercePermission::PRODUCT_VIEW]))->toBeTrue();
});

test('permission gate denies an enum case the user was not granted', function () {
    expect(Gate::forUser($this->user)->allows('permission', EcommercePermission::PRODUCT_DELETE))->toBeFalse();
});

test('permission gate allows any actor when they are an owner, regardless of granted permissions', function () {
    $owner = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);

    expect(Gate::forUser($owner)->allows('permission', EcommercePermission::PRODUCT_DELETE))->toBeTrue();
});

test('permission gate allows an api key holding the ability', function () {
    $apiKey = ApiKey::factory()->create([
        'company_id' => $this->company->id,
        'abilities' => [EcommercePermission::PRODUCT_VIEW->value],
    ]);

    expect(Gate::forUser($apiKey)->allows('permission', EcommercePermission::PRODUCT_VIEW))->toBeTrue()
        ->and(Gate::forUser($apiKey)->allows('permission', EcommercePermission::PRODUCT_DELETE))->toBeFalse();
});

test('permission gate denies an api key without abilities', function () {
    $apiKey = ApiKey::factory()->create(['company_id' => $this->company->id, 'abilities' => null]);

    expect(Gate::forUser($apiKey)->allows('permission', EcommercePermission::PRODUCT_VIEW))->toBeFalse();
});
