<?php

use Domains\Ecommerce\Models\Brand;
use Domains\Shared\Enums\ProductStatus;
use Domains\Shared\Models\Category;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->company()->nullable(false);
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('app_code');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('canonical_url')->nullable();
            $table->foreignIdFor(Brand::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Category::class)->nullable()->constrained()->nullOnDelete();
            $table->string('sku', 100)->nullable()->index();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->string('currency', 3)->nullable()->default('USD');
            $table->unsignedInteger('stock_quantity')->nullable()->default(0);
            $table->unsignedInteger('reorder_threshold')->nullable();
            $table->boolean('track_inventory')->nullable()->default(true);
            $table->string('status', 20)->nullable()->default(ProductStatus::DRAFT->value);
            $table->json('tags')->nullable();
            $table->editor();
            $table->timestamps();

            $table->unique(['company_id', 'slug']);
            $table->unique(['company_id', 'sku']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
