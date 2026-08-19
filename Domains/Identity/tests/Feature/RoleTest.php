<?php

use Domains\Identity\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a role belongs to a company', function () {
    $company = Company::factory()->create();
    $role = $company->roles()->create(['name' => 'Owner', 'guard_name' => 'web']);

    expect($role->company)->toBeInstanceOf(Company::class)
        ->and($role->company->id)->toBe($company->id);
});

test('role names are unique per company, not globally', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $companyA->roles()->create(['name' => 'Owner', 'guard_name' => 'web']);
    $roleB = $companyB->roles()->create(['name' => 'Owner', 'guard_name' => 'web']);

    expect($roleB->exists)->toBeTrue();
    $this->assertDatabaseCount('roles', 2);
});
