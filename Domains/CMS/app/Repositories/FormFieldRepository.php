<?php

namespace Domains\CMS\Repositories;

use Domains\CMS\Models\FormField;
use Domains\Core\Repositories\BaseRepository;

class FormFieldRepository extends BaseRepository
{
    protected string $model = FormField::class;
}
