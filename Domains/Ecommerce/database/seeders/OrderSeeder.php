<?php

namespace Domains\Ecommerce\Database\Seeders;

use Domains\Ecommerce\Actions\AddOrderItem;
use Domains\Ecommerce\Enums\OrderStatus;
use Domains\Ecommerce\Models\Order;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Customer;
use Domains\Shared\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Seeds 200 demo orders (1-4 line items each, spread over the last 90 days, with a
 * realistic status mix) for every existing company, against that company's product
 * catalog. Run standalone — not part of the default seed chain, and depends on
 * ProductSeeder having already run — via:
 * `php artisan db:seed --class="Domains\Ecommerce\Database\Seeders\OrderSeeder"`.
 *
 * Not idempotent: intended for a fresh dev database.
 */
class OrderSeeder extends Seeder
{
    protected int $ordersPerCompany = 200;

    protected int $customersPerCompany = 20;

    /**
     * Roughly matches a typical order-status funnel.
     *
     * @var array<string, int>
     */
    protected array $statusWeights = [
        OrderStatus::PENDING->value => 10,
        OrderStatus::PROCESSING->value => 15,
        OrderStatus::COMPLETED->value => 60,
        OrderStatus::CANCELLED->value => 10,
        OrderStatus::REFUNDED->value => 5,
    ];

    public function run(): void
    {
        $addOrderItem = app(AddOrderItem::class);

        Company::all()->each(function (Company $company) use ($addOrderItem) {
            $products = Product::query()->where('company_id', $company->id)->get();

            if ($products->isEmpty()) {
                $this->command?->warn("Skipping orders for company #{$company->id}: no products found. Run ProductSeeder first.");

                return;
            }

            $customers = $this->customersFor($company);

            for ($i = 0; $i < $this->ordersPerCompany; $i++) {
                $order = Order::factory()->create([
                    'company_id' => $company->id,
                    'customer_id' => $customers->random()->id,
                ]);

                foreach (range(1, fake()->numberBetween(1, 4)) as $ignored) {
                    try {
                        $addOrderItem->handle($order, $products->random(), fake()->numberBetween(1, 3));
                    } catch (Throwable) {
                        // Out of stock or similar — just skip this line item.
                        continue;
                    }
                }

                $status = $this->weightedStatus();
                $isClosed = in_array($status, [OrderStatus::CANCELLED->value, OrderStatus::REFUNDED->value], true);
                $createdAt = Carbon::now()
                    ->subDays(fake()->numberBetween(0, 89))
                    ->subMinutes(fake()->numberBetween(0, 1439));

                $order->refresh()->forceFill([
                    'status' => $status,
                    'cancelled_at' => $isClosed ? $createdAt->copy()->addHours(fake()->numberBetween(1, 48)) : null,
                    'created_at' => $createdAt,
                ])->save();
            }

            $this->command?->info("Seeded {$this->ordersPerCompany} orders for company #{$company->id} ({$company->name}).");
        });
    }

    /**
     * @return Collection<int, Customer>
     */
    protected function customersFor(Company $company)
    {
        $customers = Customer::query()->where('company_id', $company->id)->get();

        if ($customers->count() < $this->customersPerCompany) {
            $customers = $customers->merge(
                Customer::factory()
                    ->count($this->customersPerCompany - $customers->count())
                    ->create(['company_id' => $company->id])
            );
        }

        return $customers;
    }

    protected function weightedStatus(): string
    {
        $total = array_sum($this->statusWeights);
        $roll = fake()->numberBetween(1, $total);
        $cumulative = 0;

        foreach ($this->statusWeights as $status => $weight) {
            $cumulative += $weight;

            if ($roll <= $cumulative) {
                return $status;
            }
        }

        return OrderStatus::PENDING->value;
    }
}
