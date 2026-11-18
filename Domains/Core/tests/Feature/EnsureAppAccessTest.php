<?php

use Domains\Core\Models\App;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a root user bypasses app-access checks regardless of subscription', function () {
    App::factory()->create(['code' => 'cms']);
    $user = User::factory()->create(['is_root' => true]);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/cms/blogs')->assertOk();
});

test('an owner is allowed when their company has the app', function () {
    $cmsApp = App::factory()->create(['code' => 'cms']);
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id, 'is_owner' => true]);
    $company->apps()->attach($cmsApp->id, ['assigned_by' => $user->id, 'assigned_at' => now()]);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/cms/blogs')->assertOk();
});

test('an owner is forbidden when their company does not have the app', function () {
    App::factory()->create(['code' => 'cms']);
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id, 'is_owner' => true]);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/cms/blogs')->assertForbidden();
});

test('a regular user requires both company subscription and an individual app grant', function () {
    // A bare route, so the user's lack of blog permissions doesn't mask the middleware's answer.
    Route::middleware(['auth:sanctum', 'app:cms'])
        ->get('/api/v1/_test/cms', fn () => response()->json(['ok' => true]));

    $cmsApp = App::factory()->create(['code' => 'cms']);
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id, 'is_owner' => false]);
    $company->apps()->attach($cmsApp->id, ['assigned_by' => $user->id, 'assigned_at' => now()]);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/_test/cms')->assertForbidden();

    $user->apps()->attach($cmsApp->id);
    $user->unsetRelation('apps');
    Cache::flush();

    $this->getJson('/api/v1/_test/cms')->assertOk();
});

test('an unauthenticated request is rejected', function () {
    $this->getJson('/api/v1/cms/blogs')->assertUnauthorized();
});

test('a second request within the cache ttl does not re-query the database for company subscription', function () {
    $cmsApp = App::factory()->create(['code' => 'cms']);
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id, 'is_owner' => true]);
    $company->apps()->attach($cmsApp->id, ['assigned_by' => $user->id, 'assigned_at' => now()]);
    Sanctum::actingAs($user);

    DB::enableQueryLog();

    $this->getJson('/api/v1/cms/blogs')->assertOk();
    DB::flushQueryLog();

    $this->getJson('/api/v1/cms/blogs')->assertOk();
    $companyLookups = collect(DB::getQueryLog())->filter(fn ($entry) => str_contains($entry['query'], 'companies'));

    expect($companyLookups)->toBeEmpty();
});

test('the middleware is domain-agnostic and works for any app code', function () {
    Route::middleware(['auth:sanctum', 'app:inventory'])
        ->get('/api/v1/_test/inventory', fn () => response()->json(['ok' => true]));

    $inventoryApp = App::factory()->create(['code' => 'inventory']);
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id, 'is_owner' => true]);
    $company->apps()->attach($inventoryApp->id, ['assigned_by' => $user->id, 'assigned_at' => now()]);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/_test/inventory')->assertOk();
});
