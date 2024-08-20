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
        Schema::create('role_module_tasks', function (Blueprint $table) {
            $table->bigInteger('role_id')->constrained('roles')->cascadeOnDelete();
            $table->string('module_task_code', 150);
            $table->unique(['role_id', 'module_task_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_module_tasks');
    }
};
