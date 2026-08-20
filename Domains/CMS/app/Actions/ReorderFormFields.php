<?php

namespace Domains\CMS\Actions;

use Domains\CMS\Models\Form;
use Domains\CMS\Models\FormField;
use Illuminate\Support\Facades\DB;

class ReorderFormFields
{
    /**
     * @param  array<int, array{id: int, sort_order: int}>  $fields
     */
    public function handle(Form $form, array $fields): void
    {
        $validIds = $form->fields()->pluck('id');

        DB::transaction(function () use ($fields, $validIds) {
            foreach ($fields as $field) {
                abort_unless($validIds->contains($field['id']), 422, 'One or more fields do not belong to this form.');

                FormField::query()->whereKey($field['id'])->update(['sort_order' => $field['sort_order']]);
            }
        });
    }
}
