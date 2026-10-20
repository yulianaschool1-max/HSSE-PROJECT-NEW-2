
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_works', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('unit', 50);
            $table->string('category', 100);

            $table->date('start_date');
            $table->date('end_date');

            $table->string('location');
            $table->string('executor');

            $table->enum('status', [
                'Belum Mulai',
                'Berlangsung',
                'Selesai',
                'Ditunda',
            ])->default('Belum Mulai');

            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['unit', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_works');
    }
};
