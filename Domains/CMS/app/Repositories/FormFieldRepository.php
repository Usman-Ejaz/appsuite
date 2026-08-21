<?php

namespace Domains\CMS\Repositories;

use Domains\CMS\Models\FormField;
use Domains\Core\Repositories\BaseRepository;
use Illuminate\Support\Arr;

class FormFieldRepository extends BaseRepository
{
    protected string $model = FormField::class;

    protected function query()
    {
        $query = parent::query();

        if ($formId = Arr::get($this->filter, 'form_id')) {
            $query->where('form_id', $formId);
        }

        return $query;
    }
}
