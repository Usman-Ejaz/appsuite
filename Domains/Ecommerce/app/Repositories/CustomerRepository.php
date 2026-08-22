<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Identity\Models\Customer;

class CustomerRepository extends BaseRepository
{
    protected string $model = Customer::class;
}
