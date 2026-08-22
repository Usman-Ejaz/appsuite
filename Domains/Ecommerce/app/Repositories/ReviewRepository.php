<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ReviewRepository extends BaseRepository
{
    protected string $model = Review::class;

    protected function query()
    {
        $query = parent::query()->with(['ecommerceProduct', 'customer']);

        if ($productId = Arr::get($this->filter, 'ecommerce_product_id')) {
            $query->where('ecommerce_product_id', $productId);
        }

        return $query;
    }

    public function findOrFail(int|Model $id)
    {
        if ($id instanceof Model) {
            return $id;
        }

        return $this->query()->findOrFail($id);
    }
}
