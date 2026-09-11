<?php

use Domains\CMS\Enums\BlogStatus;
use Domains\Identity\Models\User;
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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->company()->nullable(false);
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->foreignIdFor(User::class, 'author_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Category::class)->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default(BlogStatus::DRAFT->value);
            $table->boolean('allow_comments')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('canonical_url')->nullable();
            $table->json('tags')->nullable();
            $table->editor();
            $table->timestamps();

            $table->unique(['company_id', 'slug']);
            $table->index(['company_id', 'status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
