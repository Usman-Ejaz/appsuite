<?php

namespace Domains\Ecommerce\Database\Seeders;

use Domains\Ecommerce\Models\Brand;
use Domains\Identity\Models\Company;
use Domains\Shared\Models\Category;
use Domains\Shared\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Seeds a demo product catalog (brands, categories, products) for every existing
 * company. Run standalone — not part of the default seed chain — via:
 * `php artisan db:seed --class="Domains\Ecommerce\Database\Seeders\ProductSeeder"`.
 *
 * Not idempotent: re-running against the same database will fail on the brand/category/
 * product slug unique constraints. Intended for a fresh dev database.
 */
class ProductSeeder extends Seeder
{
    protected int $productsPerCompany = 50;

    protected int $brandsPerCompany = 5;

    protected int $categoriesPerCompany = 6;

    public function run(): void
    {
        Company::all()->each(function (Company $company) {
            $brands = Brand::factory()->count($this->brandsPerCompany)->create(['company_id' => $company->id]);
            $categories = Category::factory()->count($this->categoriesPerCompany)->create(['company_id' => $company->id]);

            Product::factory()
                ->ecommerce()
                ->count($this->productsPerCompany)
                ->state(fn () => [
                    'company_id' => $company->id,
                    'brand_id' => $brands->random()->id,
                    'category_id' => $categories->random()->id,
                    'stock_quantity' => fake()->numberBetween(500, 5000),
                ])
                ->create();

            $this->command?->info("Seeded {$this->productsPerCompany} products for company #{$company->id} ({$company->name}).");
        });
    }
}
