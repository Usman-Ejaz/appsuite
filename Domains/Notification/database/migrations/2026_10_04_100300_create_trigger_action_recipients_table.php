<?php

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
        Schema::create('trigger_action_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TriggerAction::class)->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->nullableMorphs('recipient'); // User or Role
            $table->string('code', 100)->nullable(); // dynamic recipient, e.g. "customer"
            $table->string('address')->nullable(); // static email or phone
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trigger_action_recipients');
    }
};
