<?php

use Domains\CMS\Models\Form;
use Domains\CMS\Models\FormField;
use Domains\CMS\Models\FormSubmission;
use Domains\Core\Models\App;
use Domains\Identity\Models\ApiKey;
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

    foreach (['cms:forms:submit', 'cms:submissions:view', 'cms:submissions:update', 'cms:submissions:delete'] as $name) {
        $permission = Permission::create([
            'app_id' => $this->cmsApp->id,
            'name' => $name,
            'code' => str($name)->afterLast(':').'_test',
            'guard_name' => 'web',
        ]);
        $this->user->givePermissionTo($permission);
    }

    Sanctum::actingAs($this->user);
    $this->form = Form::factory()->create(['company_id' => $this->company->id, 'is_active' => true]);
});

test('a valid submission is recorded', function () {
    FormField::factory()->create([
        'form_id' => $this->form->id, 'company_id' => $this->company->id,
        'name' => 'email', 'type' => 'email', 'is_required' => true,
    ]);

    $this->postJson("/api/v1/forms/{$this->form->id}/submit", ['data' => ['email' => 'visitor@example.com']])
        ->assertCreated()
        ->assertJsonPath('data.data.email', 'visitor@example.com');
});

test('a missing required field is rejected', function () {
    FormField::factory()->create([
        'form_id' => $this->form->id, 'company_id' => $this->company->id,
        'name' => 'email', 'type' => 'email', 'is_required' => true,
    ]);

    $this->postJson("/api/v1/forms/{$this->form->id}/submit", ['data' => []])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['data.email']);
});

test('a value of the wrong type is rejected', function () {
    FormField::factory()->create([
        'form_id' => $this->form->id, 'company_id' => $this->company->id,
        'name' => 'age', 'type' => 'number', 'is_required' => true,
    ]);

    $this->postJson("/api/v1/forms/{$this->form->id}/submit", ['data' => ['age' => 'not-a-number']])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['data.age']);
});

test('custom validation_rules are enforced', function () {
    FormField::factory()->create([
        'form_id' => $this->form->id, 'company_id' => $this->company->id,
        'name' => 'message', 'type' => 'textarea', 'is_required' => true,
        'validation_rules' => ['max' => 5],
    ]);

    $this->postJson("/api/v1/forms/{$this->form->id}/submit", ['data' => ['message' => 'this is too long']])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['data.message']);
});

test('undeclared data keys are silently dropped, not stored', function () {
    FormField::factory()->create([
        'form_id' => $this->form->id, 'company_id' => $this->company->id,
        'name' => 'email', 'type' => 'email', 'is_required' => true,
    ]);

    $response = $this->postJson("/api/v1/forms/{$this->form->id}/submit", [
        'data' => ['email' => 'visitor@example.com', 'unexpected_field' => 'hello'],
    ])->assertCreated();

    expect($response->json('data.data'))->not->toHaveKey('unexpected_field');
});

test('submitting to an inactive form is rejected', function () {
    $this->form->update(['is_active' => false]);

    $this->postJson("/api/v1/forms/{$this->form->id}/submit", ['data' => []])->assertNotFound();
});

test('a submission belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $otherForm = Form::factory()->create(['company_id' => $otherCompany->id]);

    $this->postJson("/api/v1/forms/{$otherForm->id}/submit", ['data' => []])->assertNotFound();
});

test('a restricted api key can submit but is blocked from viewing, updating, or deleting submissions', function () {
    $apiKey = ApiKey::factory()->create([
        'company_id' => $this->company->id,
        'abilities' => ['cms:forms:submit', 'cms:submissions:view', 'cms:submissions:update', 'cms:submissions:delete'],
    ]);
    Sanctum::actingAs($apiKey);

    $this->postJson("/api/v1/forms/{$this->form->id}/submit", ['data' => []])->assertCreated();

    $submission = FormSubmission::query()->where('form_id', $this->form->id)->firstOrFail();

    $this->getJson("/api/v1/forms/{$this->form->id}/submissions")->assertForbidden();
    $this->getJson("/api/v1/forms/{$this->form->id}/submissions/{$submission->id}")->assertForbidden();
    $this->putJson("/api/v1/forms/{$this->form->id}/submissions/{$submission->id}", ['status' => 'read'])->assertForbidden();
    $this->deleteJson("/api/v1/forms/{$this->form->id}/submissions/{$submission->id}")->assertForbidden();
});

test('a user can list, view, update the status of, and delete submissions', function () {
    $submission = FormSubmission::factory()->create(['form_id' => $this->form->id, 'company_id' => $this->company->id]);

    $this->getJson("/api/v1/forms/{$this->form->id}/submissions")->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/forms/{$this->form->id}/submissions/{$submission->id}", ['status' => 'read'])
        ->assertOk()
        ->assertJsonPath('data.status', 'read');

    $this->deleteJson("/api/v1/forms/{$this->form->id}/submissions/{$submission->id}")->assertNoContent();
});
