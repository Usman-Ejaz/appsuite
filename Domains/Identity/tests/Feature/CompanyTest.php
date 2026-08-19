<?php

use Domains\Identity\Enums\CompanyStatus;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a company can be created with its fillable attributes', function () {
    $company = Company::factory()->create([
        'name' => 'Acme Inc',
        'status' => CompanyStatus::ACTIVE,
    ]);

    expect($company->name)->toBe('Acme Inc')
        ->and($company->status)->toBe(CompanyStatus::ACTIVE);

    $this->assertDatabaseHas('companies', ['name' => 'Acme Inc']);
});

test('a company has many roles', function () {
    $company = Company::factory()->create();

    $company->roles()->create(['name' => 'Owner', 'guard_name' => 'web']);
    $company->roles()->create(['name' => 'Member', 'guard_name' => 'web']);

    expect($company->roles()->count())->toBe(2)
        ->and($company->roles->first())->toBeInstanceOf(Role::class);
});
