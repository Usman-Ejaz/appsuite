<?php

use Domains\Notification\Models\Trigger;
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
        // One row per channel: a trigger notifying by email and SMS has two actions, each with
        // its own content.
        Schema::create('trigger_actions', function (Blueprint $table) {
            $table->id();
            $table->company()->nullable(false);
            $table->foreignIdFor(Trigger::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('channel', 50);
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->json('conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->editor();
            $table->timestamps();

            $table->index(['company_id', 'trigger_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trigger_actions');
    }
};
