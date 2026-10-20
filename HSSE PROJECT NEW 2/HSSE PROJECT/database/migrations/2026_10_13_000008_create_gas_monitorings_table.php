<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('gas_monitorings')) {
            Schema::create('gas_monitorings', function (Blueprint $table) {
                $table->id();
                $table->date('measurement_date')->index();
                $table->string('well_pad', 50)->index();
                $table->decimal('co_level', 8, 2)->default(0);
                $table->decimal('h2s_level', 8, 2)->default(0);
                $table->decimal('pressure', 8, 2)->default(0);
                $table->string('co_status', 20)->default('Sesuai');
                $table->string('h2s_status', 20)->default('Sesuai');
                $table->string('pressure_status', 20)->default('Sesuai');
                $table->text('notes')->nullable();
                $table->string('recorded_by', 100)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gas_monitorings');
    }
};

