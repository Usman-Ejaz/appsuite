<?php

namespace Domains\Ecommerce\Actions;

use Domains\Ecommerce\Models\Collection;
use Domains\Ecommerce\Models\EcommerceProduct;

class SyncCollectionProducts
{
    /**
     * @param  array<int, array{id: int, sort_order?: int}>  $products
     */
    public function handle(Collection $collection, array $products): void
    {
        $validIds = EcommerceProduct::query()->pluck('id');

        $pivotData = [];

        foreach ($products as $product) {
            abort_unless($validIds->contains($product['id']), 422, 'One or more products do not belong to this company.');

            $pivotData[$product['id']] = ['sort_order' => $product['sort_order'] ?? 0];
        }

        $collection->products()->sync($pivotData);
    }
}
