<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Master Perusahaan
        if (!Schema::hasTable('daily_work_companies')) {
            Schema::create('daily_work_companies', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Tabel Master PTW Classification
        if (!Schema::hasTable('ptw_classifications')) {
            Schema::create('ptw_classifications', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Tabel Master SIKKA
        if (!Schema::hasTable('sikkas')) {
            Schema::create('sikkas', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. Modifikasi kolom pada daily_works
        if (Schema::hasTable('daily_works')) {
            Schema::table('daily_works', function (Blueprint $table) {
                if (!Schema::hasColumn('daily_works', 'ptw_classification')) {
                    $table->string('ptw_classification', 100)->nullable()->after('location');
                }
                if (!Schema::hasColumn('daily_works', 'sikka')) {
                    $table->string('sikka', 100)->nullable()->after('ptw_classification');
                }
            });

            // Ubah category dan executor agar nullable (tidak memicu SQL error bila form baru tidak mengisi)
            DB::statement("ALTER TABLE daily_works MODIFY COLUMN category VARCHAR(100) NULL DEFAULT 'Umum'");
            DB::statement("ALTER TABLE daily_works MODIFY COLUMN executor VARCHAR(255) NULL DEFAULT '-'");
        }

        // 5. Seed Master Data Awal
        $now = now();

        $initialCompanies = [
            'GDE (Internal)',
            'BUJP (Security)',
            'PJSE (Utility)',
            'JOTRE',
            'PMC',
        ];
        foreach ($initialCompanies as $comp) {
            $exists = DB::table('daily_work_companies')->where('name', $comp)->exists();
            if (!$exists) {
                DB::table('daily_work_companies')->insert([
                    'name' => $comp,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $initialPtws = [
            'Cold Work',
            'Hot Work',
        ];
        foreach ($initialPtws as $ptw) {
            $exists = DB::table('ptw_classifications')->where('name', $ptw)->exists();
            if (!$exists) {
                DB::table('ptw_classifications')->insert([
                    'name' => $ptw,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $initialSikkas = [
            'Ruang Terbatas',
            'Ketinggian',
            'Penggalian',
            'Listrik',
            'Isolasi Energi',
            'Lifting',
        ];
        foreach ($initialSikkas as $sikka) {
            $exists = DB::table('sikkas')->where('name', $sikka)->exists();
            if (!$exists) {
                DB::table('sikkas')->insert([
                    'name' => $sikka,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 6. Sinkronisasi data lama daily_works
        if (Schema::hasTable('daily_works')) {
            // Hapus / petakan jika ada data yang masih menggunakan SOKKO
            DB::table('daily_works')->where('unit', 'SOKKO')->update(['unit' => 'PMC']);

            // Berikan nilai awal ptw_classification dan sikka pada record yang masih NULL
            DB::table('daily_works')->whereNull('ptw_classification')->orWhere('ptw_classification', '')->update([
                'ptw_classification' => 'Cold Work',
            ]);
            DB::table('daily_works')->whereNull('sikka')->orWhere('sikka', '')->update([
                'sikka' => 'Ruang Terbatas',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('daily_works')) {
            Schema::table('daily_works', function (Blueprint $table) {
                if (Schema::hasColumn('daily_works', 'sikka')) {
                    $table->dropColumn('sikka');
                }
                if (Schema::hasColumn('daily_works', 'ptw_classification')) {
                    $table->dropColumn('ptw_classification');
                }
            });
        }

        Schema::dropIfExists('sikkas');
        Schema::dropIfExists('ptw_classifications');
        Schema::dropIfExists('daily_work_companies');
    }
};

