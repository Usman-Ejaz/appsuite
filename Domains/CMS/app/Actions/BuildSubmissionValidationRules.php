<?php

namespace Domains\CMS\Actions;

use Domains\CMS\Models\Form;
use Illuminate\Validation\Rule;

class BuildSubmissionValidationRules
{
    /**
     * Build Laravel validation rules dynamically from a Form's own
     * FormField rows, so submission validation is data-driven rather than
     * requiring a bespoke rule set per form.
     *
     * @return array<string, array<int, mixed>>
     */
    public function handle(Form $form): array
    {
        $rules = [];

        foreach ($form->fields()->where('is_active', true)->get() as $field) {
            if ($field->type->isLayoutOnly()) {
                continue;
            }

            $fieldRules = $field->is_required ? ['required'] : ['nullable'];
            $fieldRules = array_merge($fieldRules, $field->type->baseRules());

            if ($field->type->supportsOptions() && ! empty($field->options)) {
                $fieldRules[] = Rule::in(collect($field->options)->pluck('value'));
            }

            foreach ($field->validation_rules ?? [] as $key => $value) {
                $fieldRules[] = match ($key) {
                    'min' => "min:{$value}",
                    'max' => "max:{$value}",
                    'regex' => "regex:{$value}",
                    'allowed_mime_types' => 'mimetypes:'.implode(',', $value),
                    default => null,
                };
            }

            $rules["data.{$field->name}"] = array_values(array_filter($fieldRules));
        }

        return $rules;
    }
}
