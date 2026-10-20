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
        // 1. Buat tabel master kontraktor jika belum ada
        if (!Schema::hasTable('contractors')) {
            Schema::create('contractors', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code', 50)->nullable()->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Tambahkan kolom contractor_id ke tabel hsse_plans jika belum ada
        if (Schema::hasTable('hsse_plans')) {
            Schema::table('hsse_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('hsse_plans', 'contractor_id')) {
                    $table->foreignId('contractor_id')
                        ->nullable()
                        ->after('unit')
                        ->constrained('contractors')
                        ->nullOnDelete();
                }
                if (!Schema::hasColumn('hsse_plans', 'contractor_name')) {
                    $table->string('contractor_name', 255)
                        ->nullable()
                        ->after('contractor_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('hsse_plans')) {
            Schema::table('hsse_plans', function (Blueprint $table) {
                if (Schema::hasColumn('hsse_plans', 'contractor_id')) {
                    $table->dropForeign(['contractor_id']);
                    $table->dropColumn('contractor_id');
                }
                if (Schema::hasColumn('hsse_plans', 'contractor_name')) {
                    $table->dropColumn('contractor_name');
                }
            });
        }

        Schema::dropIfExists('contractors');
    }
};
