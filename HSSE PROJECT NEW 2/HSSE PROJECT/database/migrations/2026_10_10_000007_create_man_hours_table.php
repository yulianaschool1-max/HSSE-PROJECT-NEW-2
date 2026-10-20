
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('man_hours', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workforce_id')
                ->nullable()
                ->constrained('workforces')
                ->nullOnDelete();

            $table->string('unit', 50);
            $table->date('work_date');
            $table->unsignedInteger('number_of_workers')->default(0);
            $table->decimal('hours_per_worker', 5, 2)->default(0);
            $table->decimal('total_hours', 12, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['unit', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('man_hours');
    }
};
