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
        Schema::create('gas_detectors', function (Blueprint $table) {
            $table->id();

            $table->string('device_code')->unique();
            $table->string('model_name');
            $table->string('serial_number')->nullable();
            $table->string('gas_type', 100)->nullable()->default('Multi-Gas (H2S, CO, O2, LEL)');
            $table->string('unit', 50)->default('PMU');
            $table->string('location')->nullable();
            $table->string('status', 50)->default('Tersedia'); // Tersedia, Sedang Digunakan, Dalam Perawatan, Perlu Kalibrasi
            $table->date('last_calibration_date')->nullable();
            $table->date('next_calibration_date')->nullable();
            $table->string('user_in_charge')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['unit', 'status']);
            $table->index(['device_code']);
            $table->index(['next_calibration_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gas_detectors');
    }
};

