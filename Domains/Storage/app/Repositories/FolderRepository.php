<?php

namespace Domains\Storage\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Storage\Models\Folder;

class FolderRepository extends BaseRepository
{
    protected string $model = Folder::class;
}
