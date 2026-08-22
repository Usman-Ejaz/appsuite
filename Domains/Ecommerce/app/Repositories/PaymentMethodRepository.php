<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Shared\Models\PaymentMethod;

class PaymentMethodRepository extends BaseRepository
{
    protected string $model = PaymentMethod::class;
}
