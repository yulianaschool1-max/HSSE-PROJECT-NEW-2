<?php

namespace Database\Seeders;

use App\Models\GasMonitoring;
use App\Models\WellPad;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GasMonitoringRealisticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi data historis pemantauan gas untuk 5 Well Pad aktif di PT Geo Dipa Energi Unit Dieng.
     */
    public function run(): void
    {
        // Pastikan well pad aktif ada
        $activePads = WellPad::where('is_active', true)->orderBy('sort_order')->get();
        if ($activePads->isEmpty()) {
            return;
        }

        // Buat data untuk rentang 1 Oktober 2026 s/d 20 Oktober 2026
        $startDate = Carbon::create(2026, 10, 1);
        $endDate = Carbon::create(2026, 10, 20);

        $now = now();
        $officers = ['Eko Prasetyo (HSSE)', 'Budi Santoso (HSSE)', 'Diana Lestari (Operator)', 'Rian Kurniawan (HSSE)'];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');

            foreach ($activePads as $pad) {
                // Cek apakah sudah ada rekaman untuk well pad dan tanggal ini
                $existing = GasMonitoring::where('well_pad', $pad->name)
                    ->whereDate('measurement_date', $dateStr)
                    ->first();

                if ($existing) {
                    continue;
                }

                // Nilai realistis dengan fluktuasi normal di Well Pad Geothermal
                // Thresholds:
                // CO: <= 25 ppm (biasanya 0.5 - 4.5 ppm)
                // O2: 19.5 - 23.5 % (biasanya 20.7 - 21.2 %)
                // H2S: <= 10 ppm (biasanya 0.0 - 2.5 ppm, sesekali 3-5 ppm)
                // LEL: <= 10 %LEL (biasanya 0.0 - 1.5 %)
                // Pressure: <= 20 barg (biasanya 8.5 - 14.5 barg)

                $seed = crc32($dateStr . $pad->name);
                mt_srand($seed);

                $co = round(mt_rand(5, 45) / 10, 1);       // 0.5 - 4.5 ppm
                $o2 = round(mt_rand(206, 213) / 10, 1);   // 20.6 - 21.3 %
                $h2s = round(mt_rand(0, 30) / 10, 1);      // 0.0 - 3.0 ppm
                $lel = round(mt_rand(0, 15) / 10, 1);      // 0.0 - 1.5 %
                $pressure = round(mt_rand(85, 145) / 10, 1); // 8.5 - 14.5 barg

                // Tambahkan satu kejadian fluktuasi mendekati batas pada Pad 29 D di tanggal 14 Okt agar grafik menarik dan informatif
                if ($pad->name === 'Pad 29 D' && $dateStr === '2026-10-14') {
                    $h2s = 7.5;
                }

                $coStatus = ($co <= 25.0) ? 'Sesuai' : 'Tidak Sesuai';
                $o2Status = ($o2 >= 19.5 && $o2 <= 23.5) ? 'Sesuai' : 'Tidak Sesuai';
                $h2sStatus = ($h2s <= 10.0) ? 'Sesuai' : 'Tidak Sesuai';
                $lelStatus = ($lel <= 10.0) ? 'Sesuai' : 'Tidak Sesuai';
                $pressureStatus = ($pressure <= 20.0) ? 'Sesuai' : 'Tidak Sesuai';

                $isOverallConforming = ($coStatus === 'Sesuai'
                    && $o2Status === 'Sesuai'
                    && $h2sStatus === 'Sesuai'
                    && $lelStatus === 'Sesuai'
                    && $pressureStatus === 'Sesuai');

                $officer = $officers[mt_rand(0, count($officers) - 1)];

                GasMonitoring::create([
                    'well_pad_id' => $pad->id,
                    'well_pad' => $pad->name,
                    'measurement_date' => $dateStr,
                    'co_level' => $co,
                    'o2_level' => $o2,
                    'h2s_level' => $h2s,
                    'lel_level' => $lel,
                    'pressure' => $pressure,
                    'co_status' => $coStatus,
                    'o2_status' => $o2Status,
                    'h2s_status' => $h2sStatus,
                    'lel_status' => $lelStatus,
                    'pressure_status' => $pressureStatus,
                    'overall_status' => $isOverallConforming ? 'Sesuai' : 'Tidak Sesuai',
                    'notes' => 'Pengukuran rutin kondisi operasional normal',
                    'recorded_by' => $officer,
                    'created_at' => $date->copy()->setTime(8, 30),
                    'updated_at' => $date->copy()->setTime(8, 30),
                ]);
            }
        }
    }
}
