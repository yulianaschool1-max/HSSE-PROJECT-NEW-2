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
        Schema::table('incidents', function (Blueprint $table) {
            if (!Schema::hasColumn('incidents', 'nomor_insiden')) {
                $table->string('nomor_insiden')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('incidents', 'tanggal_waktu')) {
                $table->dateTime('tanggal_waktu')->nullable()->after('nomor_insiden');
            }
            if (!Schema::hasColumn('incidents', 'klasifikasi_area')) {
                $table->string('klasifikasi_area', 100)->nullable()->default('Kecelakaan Panas Bumi')->after('tanggal_waktu');
            }
            if (!Schema::hasColumn('incidents', 'kategori_umum')) {
                $table->json('kategori_umum')->nullable()->after('klasifikasi_area');
            }
            if (!Schema::hasColumn('incidents', 'kategori_ebtke')) {
                $table->json('kategori_ebtke')->nullable()->after('kategori_umum');
            }
            if (!Schema::hasColumn('incidents', 'lokasi_kejadian')) {
                $table->string('lokasi_kejadian')->nullable()->after('kategori_ebtke');
            }
            if (!Schema::hasColumn('incidents', 'komponen_terkait')) {
                $table->string('komponen_terkait', 100)->nullable()->after('lokasi_kejadian');
            }
            if (!Schema::hasColumn('incidents', 'deskripsi_kejadian')) {
                $table->text('deskripsi_kejadian')->nullable()->after('komponen_terkait');
            }
            if (!Schema::hasColumn('incidents', 'tindakan_perbaikan')) {
                $table->text('tindakan_perbaikan')->nullable()->after('deskripsi_kejadian');
            }
            if (!Schema::hasColumn('incidents', 'penanggung_jawab')) {
                $table->string('penanggung_jawab', 100)->nullable()->after('tindakan_perbaikan');
            }
            if (!Schema::hasColumn('incidents', 'target_penyelesaian')) {
                $table->date('target_penyelesaian')->nullable()->after('penanggung_jawab');
            }
            if (!Schema::hasColumn('incidents', 'status_laporan')) {
                $table->string('status_laporan', 50)->default('Preliminary')->after('target_penyelesaian');
            }
            if (!Schema::hasColumn('incidents', 'jumlah_hari_hilang')) {
                $table->unsignedInteger('jumlah_hari_hilang')->default(0)->after('status_laporan');
            }
            if (!Schema::hasColumn('incidents', 'dokumen_investigasi')) {
                $table->string('dokumen_investigasi')->nullable()->after('jumlah_hari_hilang');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_insiden',
                'tanggal_waktu',
                'klasifikasi_area',
                'kategori_umum',
                'kategori_ebtke',
                'lokasi_kejadian',
                'komponen_terkait',
                'deskripsi_kejadian',
                'tindakan_perbaikan',
                'penanggung_jawab',
                'target_penyelesaian',
                'status_laporan',
                'jumlah_hari_hilang',
                'dokumen_investigasi',
            ]);
        });
    }
};
