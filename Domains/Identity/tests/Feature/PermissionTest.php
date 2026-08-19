<?php

use Domains\Core\Models\App;
use Domains\Identity\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a permission can be created with its fillable attributes and belongs to an app', function () {
    $app = App::factory()->create();

    $permission = Permission::create([
        'app_id' => $app->id,
        'name' => 'contacts.view',
        'label' => 'View Contacts',
        'code' => 'contacts_view',
        'guard_name' => 'web',
    ]);

    expect($permission->label)->toBe('View Contacts')
        ->and($permission->app)->toBeInstanceOf(App::class)
        ->and($permission->app->id)->toBe($app->id);
});
