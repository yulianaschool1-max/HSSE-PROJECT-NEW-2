
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('category', 100);
            $table->string('unit', 50);

            $table->date('event_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->string('location');
            $table->string('pic');

            $table->enum('status', [
                'Direncanakan',
                'Berlangsung',
                'Selesai',
                'Dibatalkan',
            ])->default('Direncanakan');

            $table->text('description')->nullable();
            $table->string('documentation')->nullable();

            $table->timestamps();

            $table->index(['event_date', 'status']);
            $table->index(['unit', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
