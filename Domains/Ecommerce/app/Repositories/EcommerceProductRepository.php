<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\EcommerceProduct;
use Domains\Shared\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EcommerceProductRepository extends BaseRepository
{
    protected string $model = EcommerceProduct::class;

    /**
     * Fields that live on the parent `products` table rather than
     * `ecommerce_products` — see EcommerceProduct's parent/child design.
     */
    protected array $basicFields = ['name', 'slug', 'description', 'thumbnail', 'is_active'];

    protected function query()
    {
        return parent::query()->with(['product', 'brand', 'category']);
    }

    /**
     * BaseRepository::findOrFail() queries the bare model, bypassing the
     * eager loads above — override it so a single-item lookup still returns
     * `product` loaded (this resource's own name/slug/price come from the
     * parent, not a native column, so without this they'd silently vanish).
     */
    public function findOrFail(int|Model $id)
    {
        if ($id instanceof Model) {
            return $id;
        }

        return $this->query()->findOrFail($id);
    }

    /**
     * Creates both the parent `Product` (basic info) and the child
     * `EcommerceProduct` (commerce info) from one flat payload, so the API
     * exposes a single product endpoint instead of two.
     */
    public function create(array $data): Model
    {
        return $this->dbTransaction(function () use ($data) {
            $product = Product::create(Arr::only($data, $this->basicFields));

            $ecommerceProduct = EcommerceProduct::create([
                ...Arr::except($data, $this->basicFields),
                'product_id' => $product->id,
            ]);

            return $ecommerceProduct->setRelation('product', $product);
        });
    }

    public function update($id, array $data): Model
    {
        return $this->dbTransaction(function () use ($id, $data) {
            $ecommerceProduct = $this->findOrFail($id);
            $ecommerceProduct->product->update(Arr::only($data, $this->basicFields));
            $ecommerceProduct->update(Arr::except($data, $this->basicFields));

            return $ecommerceProduct->fresh(['product', 'brand', 'category']);
        });
    }

    /**
     * Deletes the parent `Product`, which cascades to the child row.
     */
    public function delete($id)
    {
        return $this->dbTransaction(function () use ($id) {
            return $this->findOrFail($id)->product->delete();
        });
    }
}
