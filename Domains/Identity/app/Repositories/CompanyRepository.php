<?php

namespace Domains\Identity\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Identity\Models\Company;

class CompanyRepository extends BaseRepository
{
    /**
     * The model associated with the repository.
     */
    protected string $model = Company::class;
}
