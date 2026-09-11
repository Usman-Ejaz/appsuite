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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->company()->nullable(false);
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('tag_line')->nullable();
            $table->string('url');
            $table->string('status');
            $table->json('setting')->nullable();
            $table->boolean('allow_comments_on_blogs')->default(true);
            $table->boolean('requires_comment_approval')->default(true);
            $table->boolean('notify_on_comments')->default(true);
            $table->boolean('spam_filtering')->default(true);
            $table->text('blocklist_keywords')->nullable();
            $table->editor();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
