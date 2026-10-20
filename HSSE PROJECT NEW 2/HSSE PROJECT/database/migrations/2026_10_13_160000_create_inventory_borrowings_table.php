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
        if (!Schema::hasTable('inventory_borrowings')) {
            Schema::create('inventory_borrowings', function (Blueprint $table) {
                $table->id();
                $table->string('borrow_code', 60)->unique();
                $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
                $table->unsignedInteger('quantity_borrowed');
                $table->unsignedInteger('quantity_returned')->default(0);
                $table->string('borrower_name', 255);
                $table->string('department', 100)->nullable();
                $table->datetime('borrow_date');
                $table->date('expected_return_date')->nullable();
                $table->datetime('actual_return_date')->nullable();
                $table->string('status', 50)->default('Dipinjam'); // 'Dipinjam', 'Sebagian Dikembalikan', 'Dikembalikan'
                $table->text('purpose')->nullable();
                $table->text('notes')->nullable();
                $table->string('recorded_by', 255)->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['status', 'borrow_date']);
                $table->index('borrower_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_borrowings');
    }
};

