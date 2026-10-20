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
        Schema::create('hsse_plan_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hsse_plan_id')->constrained('hsse_plans')->cascadeOnDelete();
            $table->text('note');
            $table->string('author', 100)->nullable();
            $table->timestamps();

            $table->index('hsse_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hsse_plan_notes');
    }
};

