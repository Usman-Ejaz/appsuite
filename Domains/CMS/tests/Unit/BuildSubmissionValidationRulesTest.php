<?php

use Domains\CMS\Actions\BuildSubmissionValidationRules;
use Domains\CMS\Models\Form;
use Domains\CMS\Models\FormField;
use Domains\Identity\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->form = Form::factory()->create(['company_id' => $this->company->id]);
});

test('a required field produces a required rule, an optional one produces nullable', function () {
    FormField::factory()->create(['form_id' => $this->form->id, 'company_id' => $this->company->id, 'name' => 'required_field', 'is_required' => true]);
    FormField::factory()->create(['form_id' => $this->form->id, 'company_id' => $this->company->id, 'name' => 'optional_field', 'is_required' => false]);

    $rules = (new BuildSubmissionValidationRules)->handle($this->form->fresh());

    expect($rules['data.required_field'])->toContain('required')
        ->and($rules['data.optional_field'])->toContain('nullable');
});

test('each field type contributes its base rule', function () {
    FormField::factory()->create(['form_id' => $this->form->id, 'company_id' => $this->company->id, 'name' => 'an_email', 'type' => 'email']);
    FormField::factory()->create(['form_id' => $this->form->id, 'company_id' => $this->company->id, 'name' => 'a_number', 'type' => 'number']);
    FormField::factory()->create(['form_id' => $this->form->id, 'company_id' => $this->company->id, 'name' => 'a_date', 'type' => 'date']);

    $rules = (new BuildSubmissionValidationRules)->handle($this->form->fresh());

    expect($rules['data.an_email'])->toContain('email')
        ->and($rules['data.a_number'])->toContain('numeric')
        ->and($rules['data.a_date'])->toContain('date');
});

test('a select field with options derives an in-rule from the option values', function () {
    FormField::factory()->create([
        'form_id' => $this->form->id, 'company_id' => $this->company->id, 'name' => 'plan', 'type' => 'select',
        'options' => [['value' => 'basic', 'label' => 'Basic'], ['value' => 'pro', 'label' => 'Pro']],
    ]);

    $rules = (new BuildSubmissionValidationRules)->handle($this->form->fresh());

    $inRule = collect($rules['data.plan'])->first(fn ($rule) => is_object($rule));

    expect($inRule)->not->toBeNull();
});

test('custom validation_rules are merged onto the base rule', function () {
    FormField::factory()->create([
        'form_id' => $this->form->id, 'company_id' => $this->company->id, 'name' => 'bio', 'type' => 'textarea',
        'validation_rules' => ['max' => 100],
    ]);

    $rules = (new BuildSubmissionValidationRules)->handle($this->form->fresh());

    expect($rules['data.bio'])->toContain('max:100');
});

test('layout-only fields never produce a rule', function () {
    FormField::factory()->create(['form_id' => $this->form->id, 'company_id' => $this->company->id, 'name' => 'heading_field', 'type' => 'heading']);
    FormField::factory()->create(['form_id' => $this->form->id, 'company_id' => $this->company->id, 'name' => 'paragraph_field', 'type' => 'paragraph']);

    $rules = (new BuildSubmissionValidationRules)->handle($this->form->fresh());

    expect($rules)->not->toHaveKey('data.heading_field')
        ->and($rules)->not->toHaveKey('data.paragraph_field');
});

test('inactive fields are excluded', function () {
    FormField::factory()->create(['form_id' => $this->form->id, 'company_id' => $this->company->id, 'name' => 'retired_field', 'is_active' => false]);

    $rules = (new BuildSubmissionValidationRules)->handle($this->form->fresh());

    expect($rules)->not->toHaveKey('data.retired_field');
});
