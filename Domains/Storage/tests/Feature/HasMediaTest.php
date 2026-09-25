<?php

use Domains\Core\Models\App;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Company, App, and User all use the HasMedia trait and accept media through the same
 * generic Domains\Storage media API as Product — no per-resource upload endpoint.
 */
uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);

    Sanctum::actingAs($this->user);
});

test('a company logo can be uploaded and retrieved via its logo relation', function () {
    Storage::fake('public');

    $response = $this->postJson('/api/v1/media', [
        'resource_type' => 'Company',
        'disk' => 'public',
        'resource_id' => $this->company->id,
        'category' => 'Logo',
        'files' => [UploadedFile::fake()->image('logo.png')],
    ]);

    $response->assertCreated()->assertJsonPath('data.0.resource_type', 'Company');

    expect($this->company->logo?->file_name)->toBe('logo.png');
});

test('an app logo can be uploaded and retrieved via its logo relation', function () {
    Storage::fake('public');

    $app = App::factory()->create();

    $response = $this->postJson('/api/v1/media', [
        'resource_type' => 'App',
        'disk' => 'public',
        'resource_id' => $app->id,
        'category' => 'Logo',
        'files' => [UploadedFile::fake()->image('app-logo.png')],
    ]);

    $response->assertCreated()->assertJsonPath('data.0.resource_type', 'App');

    expect($app->logo?->file_name)->toBe('app-logo.png');
});

test('a user avatar can be uploaded and retrieved via its avatar relation', function () {
    Storage::fake('public');

    $response = $this->postJson('/api/v1/media', [
        'resource_type' => 'User',
        'disk' => 'public',
        'resource_id' => $this->user->id,
        'category' => 'Avatar',
        'files' => [UploadedFile::fake()->image('avatar.png')],
    ]);

    $response->assertCreated()->assertJsonPath('data.0.resource_type', 'User');

    expect($this->user->avatar?->file_name)->toBe('avatar.png');
});

test('uploading a second avatar replaces which one is returned as the current avatar', function () {
    Storage::fake('public');

    $this->postJson('/api/v1/media', [
        'resource_type' => 'User',
        'disk' => 'public',
        'resource_id' => $this->user->id,
        'category' => 'Avatar',
        'files' => [UploadedFile::fake()->image('first.png')],
    ])->assertCreated();

    $this->postJson('/api/v1/media', [
        'resource_type' => 'User',
        'disk' => 'public',
        'resource_id' => $this->user->id,
        'category' => 'Avatar',
        'files' => [UploadedFile::fake()->image('second.png')],
    ])->assertCreated();

    expect($this->user->media()->where('category', 'Avatar')->count())->toBe(2)
        ->and($this->user->avatar?->file_name)->toBe('second.png');
});

test('a resource type not registered in the morph map is rejected', function () {
    Storage::fake('public');

    $this->postJson('/api/v1/media', [
        'resource_type' => 'not-a-registered-type',
        'disk' => 'public',
        'resource_id' => $this->user->id,
        'category' => 'Avatar',
        'files' => [UploadedFile::fake()->image('avatar.png')],
    ])->assertUnprocessable();
});
