<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('issue_follow_ups')) {
            Schema::table('issue_follow_ups', function (Blueprint $table) {
                if (!Schema::hasColumn('issue_follow_ups', 'hsse_division')) {
                    // Nullable agar data lama yang belum memiliki divisi tidak diisi sembarangan (ditandai sebagai Belum Diklasifikasikan)
                    $table->string('hsse_division', 20)->nullable()->after('title');
                    $table->index('hsse_division', 'issue_follow_ups_hsse_division_idx');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('issue_follow_ups') && Schema::hasColumn('issue_follow_ups', 'hsse_division')) {
            Schema::table('issue_follow_ups', function (Blueprint $table) {
                $table->dropIndex('issue_follow_ups_hsse_division_idx');
                $table->dropColumn('hsse_division');
            });
        }
    }
};

