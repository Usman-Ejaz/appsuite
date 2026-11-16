<?php

use Domains\Notification\Models\Trigger;
use Domains\Notification\Models\TriggerAction;
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
        // A log of every trigger notification sent or failed, for every channel.
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->company()->nullable(false);
            $table->foreignIdFor(Trigger::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(TriggerAction::class)->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 50);
            $table->nullableMorphs('notifiable');
            $table->string('address')->nullable();
            $table->nullableMorphs('resource');
            $table->json('data')->nullable();
            $table->string('status', 50);
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
