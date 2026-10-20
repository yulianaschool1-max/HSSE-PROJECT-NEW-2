<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('operational_units')) {
            Schema::table('operational_units', function (Blueprint $table) {
                if (!Schema::hasColumn('operational_units', 'manual_accumulated_hours')) {
                    $table->decimal('manual_accumulated_hours', 14, 2)->nullable()->after('default_hours');
                }
                if (!Schema::hasColumn('operational_units', 'accumulated_hours_adjustment')) {
                    $table->decimal('accumulated_hours_adjustment', 14, 2)->default(0.00)->after('manual_accumulated_hours');
                }
                if (!Schema::hasColumn('operational_units', 'accumulated_hours_baseline')) {
                    $table->decimal('accumulated_hours_baseline', 14, 2)->default(0.00)->after('accumulated_hours_adjustment');
                }
                if (!Schema::hasColumn('operational_units', 'adjustment_notes')) {
                    $table->string('adjustment_notes', 255)->nullable()->after('accumulated_hours_baseline');
                }
                if (!Schema::hasColumn('operational_units', 'last_adjusted_at')) {
                    $table->timestamp('last_adjusted_at')->nullable()->after('adjustment_notes');
                }
                if (!Schema::hasColumn('operational_units', 'adjusted_by')) {
                    $table->string('adjusted_by', 150)->nullable()->after('last_adjusted_at');
                }
            });
        }

        if (!Schema::hasTable('unit_man_hour_adjustments')) {
            Schema::create('unit_man_hour_adjustments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('operational_unit_id')
                    ->constrained('operational_units')
                    ->cascadeOnDelete();
                $table->string('unit_name', 100);
                $table->decimal('previous_accumulated_hours', 14, 2)->default(0.00);
                $table->decimal('new_accumulated_hours', 14, 2)->default(0.00);
                $table->decimal('adjustment_delta', 14, 2)->default(0.00);
                $table->text('notes')->nullable();
                $table->string('adjusted_by', 150)->nullable();
                $table->timestamps();

                $table->index(['operational_unit_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_man_hour_adjustments');

        if (Schema::hasTable('operational_units')) {
            Schema::table('operational_units', function (Blueprint $table) {
                $cols = [
                    'manual_accumulated_hours',
                    'accumulated_hours_adjustment',
                    'accumulated_hours_baseline',
                    'adjustment_notes',
                    'last_adjusted_at',
                    'adjusted_by',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('operational_units', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

