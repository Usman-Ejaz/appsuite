<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\Campaign;

class CampaignRepository extends BaseRepository
{
    protected string $model = Campaign::class;
}
