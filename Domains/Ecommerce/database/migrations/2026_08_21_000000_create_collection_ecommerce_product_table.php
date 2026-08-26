<?php

use Domains\Ecommerce\Models\Collection;
use Domains\Ecommerce\Models\EcommerceProduct;
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
        Schema::create('ecom_product_collection', function (Blueprint $table) {
            $table->foreignIdFor(Collection::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(EcommerceProduct::class)->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            // $table->unique(['collection_id', 'ecommerce_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_ecommerce_product');
    }
};
