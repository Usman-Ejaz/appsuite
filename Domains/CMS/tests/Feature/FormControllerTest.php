<?php

use Domains\CMS\Models\Form;
use Domains\CMS\Models\FormField;
use Domains\Core\Models\App;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->cmsApp = App::factory()->create(['code' => 'cms']);
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    $this->company->apps()->attach($this->cmsApp->id, ['assigned_by' => $this->user->id, 'assigned_at' => now()]);

    foreach (['cms:forms:view', 'cms:forms:create', 'cms:forms:update', 'cms:forms:delete'] as $name) {
        $permission = Permission::create([
            'app_id' => $this->cmsApp->id,
            'name' => $name,
            'guard_name' => 'web',
        ]);
        $this->user->givePermissionTo($permission);
    }

    Sanctum::actingAs($this->user);
});

test('a form can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/cms/forms', ['name' => 'Contact Us', 'slug' => 'contact-us']);
    $response->assertCreated()->assertJsonPath('data.name', 'Contact Us');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/cms/forms')->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/cms/forms/{$id}", ['name' => 'Contact Sales'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Contact Sales');

    $this->deleteJson("/api/v1/cms/forms/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('forms', ['id' => $id]);
});

test('a form can be updated with its own slug', function () {
    $id = $this->postJson('/api/v1/cms/forms', ['name' => 'Contact Us', 'slug' => 'contact-us'])->assertCreated()->json('data.id');

    $this->putJson("/api/v1/cms/forms/{$id}", ['name' => 'Contact Sales', 'slug' => 'contact-us'])
        ->assertOk()
        ->assertJsonPath('data.slug', 'contact-us');
});

test('a form cannot take the slug of another form', function () {
    $this->postJson('/api/v1/cms/forms', ['name' => 'Contact Us', 'slug' => 'contact-us'])->assertCreated();
    $id = $this->postJson('/api/v1/cms/forms', ['name' => 'Support', 'slug' => 'support'])->assertCreated()->json('data.id');

    $this->putJson("/api/v1/cms/forms/{$id}", ['slug' => 'contact-us'])->assertUnprocessable();
});

test('a form belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $form = Form::factory()->create(['company_id' => $otherCompany->id]);

    $this->getJson("/api/v1/cms/forms/{$form->id}")->assertNotFound();
    $this->putJson("/api/v1/cms/forms/{$form->id}", ['name' => 'Hijacked'])->assertNotFound();
    $this->deleteJson("/api/v1/cms/forms/{$form->id}")->assertNotFound();
});

test('a user without permission cannot manage forms', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id]);
    $unprivilegedUser->apps()->attach($this->cmsApp->id);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson('/api/v1/cms/forms', ['name' => 'Contact Us', 'slug' => 'contact-us'])->assertForbidden();
});

test('slug uniqueness is scoped per company', function () {
    Form::factory()->create(['company_id' => $this->company->id, 'slug' => 'contact-us']);

    $this->postJson('/api/v1/cms/forms', ['name' => 'Another', 'slug' => 'contact-us'])
        ->assertUnprocessable();
});

test('getting a form returns its fields ordered by sort_order', function () {
    $form = Form::factory()->create(['company_id' => $this->company->id]);
    FormField::factory()->create(['form_id' => $form->id, 'company_id' => $this->company->id, 'name' => 'second', 'sort_order' => 2]);
    FormField::factory()->create(['form_id' => $form->id, 'company_id' => $this->company->id, 'name' => 'first', 'sort_order' => 1]);

    $include = json_encode([['name' => 'fields']]);

    $response = $this->getJson("/api/v1/cms/forms/{$form->id}?".http_build_query(['include' => $include]))->assertOk();

    expect($response->json('data.fields.0.name'))->toBe('first')
        ->and($response->json('data.fields.1.name'))->toBe('second');
});
