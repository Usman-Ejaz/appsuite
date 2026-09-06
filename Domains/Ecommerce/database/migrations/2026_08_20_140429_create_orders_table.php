<?php

use Domains\Ecommerce\Models\Coupon;
use Domains\Identity\Models\Customer;
use Domains\Shared\Models\PaymentMethod;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->company()->nullable(false);
            $table->foreignIdFor(Customer::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(PaymentMethod::class)->nullable()->constrained()->restrictOnDelete();
            $table->foreignIdFor(Coupon::class)->nullable()->constrained()->nullOnDelete();
            $table->string('code', 40)->index();
            $table->string('status', 20)->default('Pending');
            $table->string('payment_status', 20)->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_total', 10, 2)->default(0);
            $table->decimal('tax_total', 10, 2)->default(0);
            $table->decimal('shipping_total', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('currency', 3)->default('PKR');
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->editor();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
