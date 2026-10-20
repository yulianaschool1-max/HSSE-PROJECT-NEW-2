<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IncidentResetSeeder extends Seeder
{
    /**
     * Kosongkan tabel incidents agar bersih dari data dummy
     * dan siap digunakan untuk uji coba input manual dari awal.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('incidents')) {
            DB::table('incidents')->truncate();
        }

        Schema::enableForeignKeyConstraints();

        $this->command?->info('Tabel incidents berhasil di-reset dan dikosongkan.');
    }
}
