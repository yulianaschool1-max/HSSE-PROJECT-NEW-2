<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'participants')) {
                $table->unsignedInteger('participants')->default(0)->after('location');
            }
            if (Schema::hasColumn('events', 'pic')) {
                $table->string('pic')->nullable()->change();
            }
            if (!Schema::hasColumn('events', 'created_by')) {
                $table->string('created_by')->nullable()->after('documentation');
            }
            if (!Schema::hasColumn('events', 'updated_by')) {
                $table->string('updated_by')->nullable()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('events', 'participants')) {
                $columnsToDrop[] = 'participants';
            }
            if (Schema::hasColumn('events', 'created_by')) {
                $columnsToDrop[] = 'created_by';
            }
            if (!Schema::hasColumn('events', 'updated_by')) {
                $columnsToDrop[] = 'updated_by';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

