<?php

use Domains\CMS\Models\Form;
use Domains\CMS\Models\FormAction;
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

test('a form action can be created, listed, updated, and deleted — schema and CRUD only, not executed', function () {
    $response = $this->postJson("/api/v1/forms/{$this->form->id}/actions", [
        'type' => 'send_email', 'config' => ['to' => ['ops@example.com']],
    ]);
    $response->assertCreated()->assertJsonPath('data.type', 'send_email');

    $id = $response->json('data.id');

    $this->getJson("/api/v1/forms/{$this->form->id}/actions")->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/forms/{$this->form->id}/actions/{$id}", ['is_active' => false])
        ->assertOk()
        ->assertJsonPath('data.is_active', false);

    $this->deleteJson("/api/v1/forms/{$this->form->id}/actions/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('form_actions', ['id' => $id]);
});

test('type and config accept any string/array for now, per the deferred execution design', function () {
    $this->postJson("/api/v1/forms/{$this->form->id}/actions", [
        'type' => 'not_yet_a_real_handler', 'config' => ['anything' => 'goes'],
    ])->assertCreated();
});

test('creating an action requires a type and a config array', function () {
    $this->postJson("/api/v1/forms/{$this->form->id}/actions", [])->assertUnprocessable();
});

test('a form action belonging to a different form is not found', function () {
    $otherForm = Form::factory()->create(['company_id' => $this->company->id]);
    $action = FormAction::factory()->create(['form_id' => $otherForm->id, 'company_id' => $this->company->id]);

    $this->getJson("/api/v1/forms/{$this->form->id}/actions/{$action->id}")->assertNotFound();
    $this->putJson("/api/v1/forms/{$this->form->id}/actions/{$action->id}", ['is_active' => false])->assertNotFound();
    $this->deleteJson("/api/v1/forms/{$this->form->id}/actions/{$action->id}")->assertNotFound();
});

test('a user without permission cannot manage form actions', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson("/api/v1/forms/{$this->form->id}/actions", [
        'type' => 'send_email', 'config' => [],
    ])->assertForbidden();
});
