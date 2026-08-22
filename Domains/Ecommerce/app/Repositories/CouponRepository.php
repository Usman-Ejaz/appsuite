<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\Coupon;
use Illuminate\Database\Eloquent\Model;

class CouponRepository extends BaseRepository
{
    protected string $model = Coupon::class;

    protected function query()
    {
        return parent::query()->with('campaign');
    }

    /**
     * BaseRepository::findOrFail() queries the bare model, bypassing the
     * eager load above — override it so a single-item lookup still returns
     * `campaign` loaded.
     */
    public function findOrFail(int|Model $id)
    {
        if ($id instanceof Model) {
            return $id;
        }

        return $this->query()->findOrFail($id);
    }
}
