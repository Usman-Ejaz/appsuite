<?php

namespace Domains\CMS\Repositories;

use Domains\CMS\Models\FormSubmission;
use Domains\Core\Repositories\BaseRepository;

class FormSubmissionRepository extends BaseRepository
{
    protected string $model = FormSubmission::class;
}
