<?php

namespace Domains\Ecommerce\Providers;

use Domains\Ecommerce\Models\Brand;
use Domains\Ecommerce\Models\Collection;
use Domains\Ecommerce\Models\OrderItem;
use Domains\Ecommerce\Models\Review;
use Domains\Shared\Models\Product;
use Illuminate\Console\Scheduling\Schedule;
use Nwidart\Modules\Support\ModuleServiceProvider;

class EcommerceServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Ecommerce';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'ecommerce';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }

    public function boot(): void
    {
        parent::boot();

        $this->registerProductRelations();
    }

    /**
     * Domains/Shared must not import Domains/Ecommerce classes, so the
     * Ecommerce-specific relations on the shared Product model (brand,
     * category's ecommerce-side counterparts, collections, reviews, order
     * items) are registered here instead of declared on the model itself.
     */
    protected function registerProductRelations(): void
    {
        Product::resolveRelationUsing('brand', fn (Product $product) => $product->belongsTo(Brand::class));

        Product::resolveRelationUsing(
            'collections',
            fn (Product $product) => $product->belongsToMany(Collection::class, 'collection_product')->withPivot('sort_order')
        );

        Product::resolveRelationUsing('reviews', fn (Product $product) => $product->hasMany(Review::class));

        Product::resolveRelationUsing('orderItems', fn (Product $product) => $product->hasMany(OrderItem::class));
    }
}
