
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();

            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->string('category', 100);

            $table->string('unit_measure', 50);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('minimum_stock')->default(0);

            $table->string('location')->nullable();
            $table->string('condition', 50)->default('Baik');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['category', 'location']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
