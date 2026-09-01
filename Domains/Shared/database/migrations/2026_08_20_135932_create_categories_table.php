<?php

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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->company()->nullable(false);
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_featured');
            $table->string('status');
            $table->string('app_code');
            $table->foreignIdFor(Category::class, 'parent_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->editor();
            $table->timestamps();

            $table->unique(['company_id', 'app_code', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
