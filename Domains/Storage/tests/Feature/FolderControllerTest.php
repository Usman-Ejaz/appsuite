<?php

use Domains\Identity\Models\Company;
use Domains\Identity\Models\User;
use Domains\Storage\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);

    Sanctum::actingAs($this->user);
});

test('a folder can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/folders', ['name' => 'Product Photos']);
    $response->assertCreated()->assertJsonPath('data.name', 'Product Photos');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/folders')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/folders/{$id}")->assertOk()->assertJsonPath('data.id', $id);

    $this->putJson("/api/v1/folders/{$id}", ['name' => 'Renamed Photos'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Renamed Photos');

    $this->deleteJson("/api/v1/folders/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('folders', ['id' => $id]);
});

test('a folder can be created nested under a parent folder', function () {
    $parent = Folder::factory()->create(['company_id' => $this->company->id]);

    $response = $this->postJson('/api/v1/folders', ['name' => 'Sub Folder', 'parent_id' => $parent->id]);

    $response->assertCreated()->assertJsonPath('data.parent_id', $parent->id);

    $this->assertDatabaseHas('folders', ['id' => $response->json('data.id'), 'parent_id' => $parent->id]);
});

test('a folder cannot be nested under itself', function () {
    $folder = Folder::factory()->create(['company_id' => $this->company->id]);

    $this->putJson("/api/v1/folders/{$folder->id}", ['parent_id' => $folder->id])
        ->assertUnprocessable();
});

test('a parent_id belonging to a different company is rejected', function () {
    $otherCompany = Company::factory()->create();
    $otherFolder = Folder::factory()->create(['company_id' => $otherCompany->id]);

    $this->postJson('/api/v1/folders', ['name' => 'Sub Folder', 'parent_id' => $otherFolder->id])
        ->assertUnprocessable();
});

test('deleting a folder does not delete its child folders, only unfiles them', function () {
    $parent = Folder::factory()->create(['company_id' => $this->company->id]);
    $child = Folder::factory()->create(['company_id' => $this->company->id, 'parent_id' => $parent->id]);

    $this->deleteJson("/api/v1/folders/{$parent->id}")->assertNoContent();

    $this->assertDatabaseHas('folders', ['id' => $child->id, 'parent_id' => null]);
});

test('a folder belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $otherFolder = Folder::factory()->create(['company_id' => $otherCompany->id]);

    $this->getJson("/api/v1/folders/{$otherFolder->id}")->assertNotFound();
    $this->putJson("/api/v1/folders/{$otherFolder->id}", ['name' => 'Hijacked'])->assertNotFound();
    $this->deleteJson("/api/v1/folders/{$otherFolder->id}")->assertNotFound();
});
