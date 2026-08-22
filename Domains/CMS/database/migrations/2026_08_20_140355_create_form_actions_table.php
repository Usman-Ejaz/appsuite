<?php

use Domains\CMS\Models\Form;
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
        Schema::create('form_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Form::class)->constrained()->cascadeOnDelete();
            $table->company()->nullable(false);
            // Not resolved/executed against anything yet — see Domains/CMS/app/Actions/SubmitForm.php
            // for the deferred FormAction execution TODO.
            $table->string('type', 100);
            $table->string('name')->nullable();
            $table->json('config');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->editor();
            $table->timestamps();

            $table->index(['form_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_actions');
    }
};
