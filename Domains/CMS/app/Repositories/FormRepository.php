<?php

namespace Domains\CMS\Repositories;

use Domains\CMS\Models\Form;
use Domains\Core\Repositories\BaseRepository;

class FormRepository extends BaseRepository
{
    protected string $model = Form::class;

    protected function query()
    {
        return parent::query()->with([
            'fields' => fn ($query) => $query->orderBy('sort_order'),
            'actions' => fn ($query) => $query->orderBy('sort_order'),
        ]);
    }
}
