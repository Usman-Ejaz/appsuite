<?php

use Domains\Identity\Models\User;
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
        Schema::create('company_apps', function (Blueprint $table) {
            $table->company();
            $table->app();
            $table->foreignIdFor(User::class, 'assigned_by')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamp('assigned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_apps');
    }
};
