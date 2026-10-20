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
        Schema::table('inventory_items', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_items', 'nama_barang')) {
                $table->string('nama_barang')->nullable()->after('item_name');
            }
            if (!Schema::hasColumn('inventory_items', 'kode_barang')) {
                $table->string('kode_barang')->nullable()->after('item_code');
            }
            if (!Schema::hasColumn('inventory_items', 'kategori')) {
                $table->string('kategori', 100)->nullable()->after('category');
            }
            if (!Schema::hasColumn('inventory_items', 'satuan')) {
                $table->string('satuan', 50)->nullable()->after('unit_measure');
            }
            if (!Schema::hasColumn('inventory_items', 'stok_sekarang')) {
                $table->unsignedInteger('stok_sekarang')->default(0)->after('stock');
            }
            if (!Schema::hasColumn('inventory_items', 'stok_minimum')) {
                $table->unsignedInteger('stok_minimum')->default(0)->after('minimum_stock');
            }
            if (!Schema::hasColumn('inventory_items', 'lokasi_default')) {
                $table->string('lokasi_default')->nullable()->after('location');
            }
            if (!Schema::hasColumn('inventory_items', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('description');
            }
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_transactions', 'jenis_transaksi')) {
                $table->string('jenis_transaksi', 20)->nullable()->after('transaction_type');
            }
            if (!Schema::hasColumn('inventory_transactions', 'jumlah')) {
                $table->unsignedInteger('jumlah')->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('inventory_transactions', 'tanggal')) {
                $table->dateTime('tanggal')->nullable()->after('transaction_date');
            }
            if (!Schema::hasColumn('inventory_transactions', 'petugas')) {
                $table->string('petugas')->nullable()->after('person_in_charge');
            }
            if (!Schema::hasColumn('inventory_transactions', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn([
                'nama_barang',
                'kode_barang',
                'kategori',
                'satuan',
                'stok_sekarang',
                'stok_minimum',
                'lokasi_default',
                'keterangan',
            ]);
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_transaksi',
                'jumlah',
                'tanggal',
                'petugas',
                'keterangan',
            ]);
        });
    }
};
