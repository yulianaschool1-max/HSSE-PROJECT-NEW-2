
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->restrictOnDelete();

            $table->enum('transaction_type', [
                'Masuk',
                'Keluar',
            ]);

            $table->unsignedInteger('quantity');

            $table->dateTime('transaction_date');

            $table->string('unit', 50)->nullable();
            $table->string('person_in_charge');
            $table->string('reference_number')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['transaction_date', 'transaction_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
