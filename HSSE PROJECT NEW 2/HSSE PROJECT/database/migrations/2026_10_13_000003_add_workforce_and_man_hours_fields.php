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
        Schema::table('workforces', function (Blueprint $table) {
            if (!Schema::hasColumn('workforces', 'record_date')) {
                $table->date('record_date')->nullable()->after('id');
            }
            if (!Schema::hasColumn('workforces', 'category')) {
                $table->string('category', 100)->default('GDE')->after('record_date');
            }
            if (!Schema::hasColumn('workforces', 'worker_count')) {
                $table->unsignedInteger('worker_count')->default(0)->after('category');
            }
            if (!Schema::hasColumn('workforces', 'hours_per_worker')) {
                $table->decimal('hours_per_worker', 5, 2)->default(8.00)->after('worker_count');
            }

            $table->index(['record_date', 'category'], 'workforces_date_category_idx');
        });

        Schema::table('man_hours', function (Blueprint $table) {
            if (!Schema::hasColumn('man_hours', 'category')) {
                $table->string('category', 100)->nullable()->after('workforce_id');
            }

            $table->index(['work_date', 'category'], 'man_hours_date_category_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('man_hours', function (Blueprint $table) {
            $table->dropIndex('man_hours_date_category_idx');
            if (Schema::hasColumn('man_hours', 'category')) {
                $table->dropColumn('category');
            }
        });

        Schema::table('workforces', function (Blueprint $table) {
            $table->dropIndex('workforces_date_category_idx');
            $columns = array_filter(
                ['record_date', 'category', 'worker_count', 'hours_per_worker'],
                fn ($col) => Schema::hasColumn('workforces', $col)
            );
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

