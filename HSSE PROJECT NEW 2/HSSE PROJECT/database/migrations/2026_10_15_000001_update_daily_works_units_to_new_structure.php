<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('daily_works')) {
            // Update unit lama ke unit baru secara aman tanpa menghapus data
            DB::table('daily_works')
                ->where('unit', 'PMU')
                ->update(['unit' => 'GDE (Internal)']);

            DB::table('daily_works')
                ->where('unit', 'Utility')
                ->update(['unit' => 'PJSE (Utility)']);

            DB::table('daily_works')
                ->where('unit', 'BISMO')
                ->update(['unit' => 'BUJP (Security)']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('daily_works')) {
            DB::table('daily_works')
                ->where('unit', 'GDE (Internal)')
                ->update(['unit' => 'PMU']);

            DB::table('daily_works')
                ->where('unit', 'PJSE (Utility)')
                ->update(['unit' => 'Utility']);

            DB::table('daily_works')
                ->where('unit', 'BUJP (Security)')
                ->update(['unit' => 'BISMO']);
        }
    }
};

