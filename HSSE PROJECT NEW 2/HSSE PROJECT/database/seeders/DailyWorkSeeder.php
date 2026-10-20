<?php

namespace Database\Seeders;

use App\Models\DailyWork;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DailyWorkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $tomorrow = Carbon::now('Asia/Jakarta')->addDays(2)->toDateString();
        $yesterday = Carbon::now('Asia/Jakarta')->subDays(1)->toDateString();

        $works = [
            // Pekerjaan Aktif Hari Ini
            [
                'name' => 'Pengecekan Rutin Turbin Unit 1',
                'unit' => 'GDE (Internal)',
                'ptw_classification' => 'Cold Work',
                'sikka' => 'Ruang Terbatas',
                'category' => 'Maintenance',
                'start_date' => $today,
                'end_date' => $tomorrow,
                'location' => 'PLTP Dieng Unit 1',
                'executor' => 'Cold Work',
                'status' => 'Berlangsung',
                'description' => 'Pemeriksaan berkala getaran turbin, temperatur oli pelumas, dan bearing generator.',
            ],
            [
                'name' => 'Perawatan Jalur Pipa Hydrant Utama',
                'unit' => 'PJSE (Utility)',
                'ptw_classification' => 'Hot Work',
                'sikka' => 'Penggalian',
                'category' => 'Safety',
                'start_date' => $yesterday,
                'end_date' => $today,
                'location' => 'Area Plant Utama',
                'executor' => 'Hot Work',
                'status' => 'Berlangsung',
                'description' => 'Pengelasan support pipa hydrant dan uji tekanan statis jaringan pipa.',
            ],
            [
                'name' => 'Patroli Keamanan & Pengawasan SIKKA Lapangan',
                'unit' => 'BUJP (Security)',
                'ptw_classification' => 'Cold Work',
                'sikka' => 'Ketinggian',
                'category' => 'Safety Patrol',
                'start_date' => $today,
                'end_date' => $today,
                'location' => 'Wellpad 28 & Sekitarnya',
                'executor' => 'Cold Work',
                'status' => 'Berlangsung',
                'description' => 'Pemeriksaan izin kerja di ketinggian kontraktor sipil dan kelengkapan APD.',
            ],
            [
                'name' => 'Instalasi Sensor Suhu Transformator',
                'unit' => 'JOTRE',
                'ptw_classification' => 'Cold Work',
                'sikka' => 'Listrik',
                'category' => 'Elektrikal',
                'start_date' => $today,
                'end_date' => $tomorrow,
                'location' => 'Switchyard 150kV',
                'executor' => 'Cold Work',
                'status' => 'Berlangsung',
                'description' => 'Pemasangan RTD sensor dan integrasi data telemetri ke ruang kontrol SCADA.',
            ],
            [
                'name' => 'Lifting & Rigging Kompresor Cadangan',
                'unit' => 'PMC',
                'ptw_classification' => 'Hot Work',
                'sikka' => 'Lifting',
                'category' => 'Mekanikal',
                'start_date' => $today,
                'end_date' => $today,
                'location' => 'Workshop Mekanikal',
                'executor' => 'Hot Work',
                'status' => 'Berlangsung',
                'description' => 'Pemindahan kompresor berkapasitas 5 ton menggunakan mobile crane dengan pengawasan rigger berlisensi.',
            ],

            // Riwayat Pekerjaan Masa Lalu
            [
                'name' => 'Pembersihan Area BOP Wellpad 28',
                'unit' => 'GDE (Internal)',
                'ptw_classification' => 'Cold Work',
                'sikka' => 'Ruang Terbatas',
                'category' => 'Cleaning',
                'start_date' => '2025-01-15',
                'end_date' => '2025-01-16',
                'location' => 'Area BOP Wellpad 28',
                'executor' => 'Cold Work',
                'status' => 'Selesai',
                'description' => 'Housekeeping area cellar dan Blowout Preventer sebelum mobilisasi peralatan penunjang.',
            ],
            [
                'name' => 'Penggantian Lampu Penerangan Emergency',
                'unit' => 'PJSE (Utility)',
                'ptw_classification' => 'Cold Work',
                'sikka' => 'Listrik',
                'category' => 'Perbaikan',
                'start_date' => '2025-01-17',
                'end_date' => '2025-01-17',
                'location' => 'Gedung Administrasi',
                'executor' => 'Cold Work',
                'status' => 'Selesai',
                'description' => 'Peremajaan baterai backup dan penggantian lampu darurat pada koridor evakuasi.',
            ],
            [
                'name' => 'Pengemasan & Labeling Limbah B3',
                'unit' => 'PMC',
                'ptw_classification' => 'Cold Work',
                'sikka' => 'Isolasi Energi',
                'category' => 'Environment',
                'start_date' => '2025-01-20',
                'end_date' => '2025-01-21',
                'location' => 'TPS Limbah B3',
                'executor' => 'Cold Work',
                'status' => 'Selesai',
                'description' => 'Penyusunan drum oli bekas dan majun terkontaminasi sesuai standar penamaan limbah.',
            ],
            [
                'name' => 'Inspeksi Rutin Sistem Pembumian Grounding',
                'unit' => 'BUJP (Security)',
                'ptw_classification' => 'Cold Work',
                'sikka' => 'Listrik',
                'category' => 'Inspeksi',
                'start_date' => '2025-01-22',
                'end_date' => '2025-01-22',
                'location' => 'Pos Induk & Pagar Perimeter',
                'executor' => 'Cold Work',
                'status' => 'Selesai',
                'description' => 'Pengukuran resistansi tanah penangkal petir pos keamanan perimeter Dieng.',
            ],
            [
                'name' => 'Perawatan Saluran Drainase Jalan Akses',
                'unit' => 'PJSE (Utility)',
                'ptw_classification' => 'Cold Work',
                'sikka' => 'Penggalian',
                'category' => 'Maintenance',
                'start_date' => '2025-01-23',
                'end_date' => '2025-01-24',
                'location' => 'Akses Utama Dieng',
                'executor' => 'Cold Work',
                'status' => 'Selesai',
                'description' => 'Pembersihan sedimen lumpur pasca hujan deras guna mencegah genangan dan longsor.',
            ],
        ];

        foreach ($works as $item) {
            DailyWork::firstOrCreate(
                ['name' => $item['name'], 'start_date' => $item['start_date']],
                $item
            );
        }
    }
}
