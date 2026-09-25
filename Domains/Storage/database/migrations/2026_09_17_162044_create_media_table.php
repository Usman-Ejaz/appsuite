<?php

use Domains\Storage\Models\Folder;
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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->company()->nullable(false);
            $table->morphs('resource');
            $table->foreignIdFor(Folder::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('title')->nullable();
            $table->boolean('starred')->default(false);
            $table->string('category')->nullable();
            $table->string('disk');
            $table->string('file_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->editor();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
