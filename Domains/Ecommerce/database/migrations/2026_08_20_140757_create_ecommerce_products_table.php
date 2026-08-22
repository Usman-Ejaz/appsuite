<?php

use Domains\Ecommerce\Models\Brand;
use Domains\Shared\Models\Category;
use Domains\Shared\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ecommerce_products', function (Blueprint $table) {
            $table->id();
            $table->company()->nullable(false);
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Brand::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Category::class)->nullable()->constrained()->nullOnDelete();
            $table->string('sku', 100);
            $table->decimal('price', 10, 2);
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->boolean('track_inventory')->default(true);
            $table->string('status', 20)->default('Draft');
            $table->json('tags')->nullable();
            $table->editor();
            $table->timestamps();

            $table->unique('product_id');
            $table->unique(['company_id', 'sku']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_products');
    }
};
