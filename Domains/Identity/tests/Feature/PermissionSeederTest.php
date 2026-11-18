<?php

use Domains\CMS\Database\Seeders\CMSDatabaseSeeder;
use Domains\CMS\Enums\CMSPermission;
use Domains\Core\Database\Seeders\CoreDatabaseSeeder;
use Domains\Ecommerce\Database\Seeders\EcommerceDatabaseSeeder;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Domains\Notification\Database\Seeders\NotificationDatabaseSeeder;
use Domains\Notification\Enums\NotificationPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CoreDatabaseSeeder::class);
});

test('each domain seeds every permission of its enum under its app', function (string $seeder, string $enum, string $appCode) {
    $this->seed($seeder);

    $names = Permission::whereRelation('app', 'code', $appCode)->pluck('name')->sort()->values()->all();

    expect($names)->toBe(collect($enum::cases())->pluck('value')->sort()->values()->all());
})->with([
    'cms' => [CMSDatabaseSeeder::class, CMSPermission::class, 'cms'],
    'ecommerce' => [EcommerceDatabaseSeeder::class, EcommercePermission::class, 'ecommerce'],
    'notification' => [NotificationDatabaseSeeder::class, NotificationPermission::class, 'system'],
]);

test('a seeded permission is named by its enum value, with its label and its description', function () {
    $this->seed(EcommerceDatabaseSeeder::class);

    $permission = Permission::where('name', 'ecommerce:payment-methods:view')->sole();

    expect($permission->label)->toBe('View Payment Methods')
        ->and($permission->guard_name)->toBe('web')
        ->and($permission->description)->toBe("View the company's configured checkout payment methods.");
});

test('re-seeding updates permissions in place', function () {
    $this->seed(CMSDatabaseSeeder::class);
    $id = Permission::where('name', CMSPermission::BLOG_VIEW->value)->value('id');

    $this->seed(CMSDatabaseSeeder::class);

    expect(Permission::count())->toBe(count(CMSPermission::cases()))
        ->and(Permission::where('name', CMSPermission::BLOG_VIEW->value)->value('id'))->toBe($id);
});

test('the permission gate matches a seeded permission by its enum case', function () {
    $this->seed(CMSDatabaseSeeder::class);
    $user = User::factory()->create(['company_id' => Company::factory()->create()->id]);

    $user->givePermissionTo(CMSPermission::BLOG_VIEW);

    expect(Gate::forUser($user)->allows('permission', CMSPermission::BLOG_VIEW))->toBeTrue()
        ->and(Gate::forUser($user)->allows('permission', CMSPermission::BLOG_DELETE))->toBeFalse();
});
