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
    $this->company->apps()->attach($this->cmsApp->id);
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);

    foreach (['cms:forms:view', 'cms:forms:update'] as $name) {
        $permission = Permission::create([
            'app_id' => $this->cmsApp->id,
            'name' => $name,
            'code' => str($name)->afterLast(':').'_test',
            'guard_name' => 'web',
        ]);
        $this->user->givePermissionTo($permission);
    }

    Sanctum::actingAs($this->user);
    $this->form = Form::factory()->create(['company_id' => $this->company->id]);
});

test('a field can be created, listed, updated, and deleted on its form', function () {
    $response = $this->postJson("/api/v1/cms/forms/{$this->form->id}/fields", [
        'label' => 'Email', 'name' => 'email', 'type' => 'email',
    ]);
    $response->assertCreated()->assertJsonPath('data.name', 'email');

    $id = $response->json('data.id');

    $this->getJson("/api/v1/cms/forms/{$this->form->id}/fields")->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/cms/forms/{$this->form->id}/fields/{$id}", ['label' => 'Your Email'])
        ->assertOk()
        ->assertJsonPath('data.label', 'Your Email');

    $this->deleteJson("/api/v1/cms/forms/{$this->form->id}/fields/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('form_fields', ['id' => $id]);
});

test('a field belonging to a different form is not found', function () {
    $otherForm = Form::factory()->create(['company_id' => $this->company->id]);
    $field = FormField::factory()->create(['form_id' => $otherForm->id, 'company_id' => $this->company->id]);

    $this->getJson("/api/v1/cms/forms/{$this->form->id}/fields/{$field->id}")->assertNotFound();
    $this->putJson("/api/v1/cms/forms/{$this->form->id}/fields/{$field->id}", ['label' => 'Hijacked'])->assertNotFound();
    $this->deleteJson("/api/v1/cms/forms/{$this->form->id}/fields/{$field->id}")->assertNotFound();
});

test('reordering rejects a field id that does not belong to the target form', function () {
    $otherForm = Form::factory()->create(['company_id' => $this->company->id]);
    $foreignField = FormField::factory()->create(['form_id' => $otherForm->id, 'company_id' => $this->company->id]);

    $this->patchJson("/api/v1/cms/forms/{$this->form->id}/fields/reorder", [
        'fields' => [['id' => $foreignField->id, 'sort_order' => 0]],
    ])->assertUnprocessable();
});

test('reordering persists new sort_order values', function () {
    $first = FormField::factory()->create(['form_id' => $this->form->id, 'company_id' => $this->company->id, 'name' => 'first', 'sort_order' => 0]);
    $second = FormField::factory()->create(['form_id' => $this->form->id, 'company_id' => $this->company->id, 'name' => 'second', 'sort_order' => 1]);

    $this->patchJson("/api/v1/cms/forms/{$this->form->id}/fields/reorder", [
        'fields' => [
            ['id' => $first->id, 'sort_order' => 1],
            ['id' => $second->id, 'sort_order' => 0],
        ],
    ])->assertNoContent();

    expect($first->fresh()->sort_order)->toBe(1)
        ->and($second->fresh()->sort_order)->toBe(0);
});

test('a user without permission cannot manage form fields', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson("/api/v1/cms/forms/{$this->form->id}/fields", [
        'label' => 'Email', 'name' => 'email', 'type' => 'email',
    ])->assertForbidden();
});
