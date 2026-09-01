<?php

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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->company()->nullable(false);
            $table->string('title');
            $table->string('slug');
            $table->longText('description');
            $table->string('status');
            $table->string('app_code');
            $table->string('meta_title')->nullable();
            $table->longText('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->editor();
            $table->timestamps();

            $table->unique(['company_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
