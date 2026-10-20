
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hsse_plans', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('category', 100);
            $table->string('unit', 50);

            $table->year('year');
            $table->decimal('target', 12, 2)->default(0);
            $table->decimal('realization', 12, 2)->default(0);
            $table->string('unit_measure', 50)->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->enum('status', [
                'Belum Mulai',
                'Berlangsung',
                'Tercapai',
                'Tidak Tercapai',
            ])->default('Belum Mulai');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['unit', 'year']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hsse_plans');
    }
};
