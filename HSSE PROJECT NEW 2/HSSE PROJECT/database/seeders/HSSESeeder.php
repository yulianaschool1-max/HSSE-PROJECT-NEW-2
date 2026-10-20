<?php

namespace Database\Seeders;

use App\Models\HssePlan;
use App\Models\HssePlanNote;
use Illuminate\Database\Seeder;

class HSSESeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kpiItems = [
            [
                'title' => 'Pemeriksaan APD Rutin',
                'category' => 'Leading',
                'unit' => 'Dieng Unit 1',
                'year' => 2025,
                'target' => 100,
                'realization' => 95,
                'unit_measure' => '%',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'status' => 'Berlangsung',
                'description' => 'Inspeksi berkala kepatuhan pemakaian APD di seluruh area operasional.',
                'notes' => [
                    ['note' => 'Cakupan area Utility dan Powerhouse diperluas untuk inspeksi triwulan II.', 'author' => 'Budi Santoso'],
                    ['note' => 'Stok helm dan rompi reflektor baru telah didistribusikan ke kontraktor.', 'author' => 'Siti Rahma'],
                ],
            ],
            [
                'title' => 'Safety Induction Pekerja Baru',
                'category' => 'Leading',
                'unit' => 'Dieng Unit 1',
                'year' => 2025,
                'target' => 100,
                'realization' => 100,
                'unit_measure' => '%',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'status' => 'Tercapai',
                'description' => 'Seluruh kontraktor dan pekerja baru wajib mengikuti safety induction sebelum memasuki area.',
                'notes' => [
                    ['note' => 'Pelaksanaan sesuai SOP, seluruh pekerja baru telah tersertifikasi induction.', 'author' => 'Ahmad Fauzi'],
                ],
            ],
            [
                'title' => 'Toolbox Meeting Harian',
                'category' => 'Leading',
                'unit' => 'Dieng Unit 1',
                'year' => 2025,
                'target' => 100,
                'realization' => 88,
                'unit_measure' => '%',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'status' => 'Berlangsung',
                'description' => 'Briefing keselamatan kerja harian sebelum pekerjaan shift dimulai.',
                'notes' => [
                    ['note' => 'Presensi digital TBM mulai diterapkan di area wellpad.', 'author' => 'Budi Santoso'],
                ],
            ],
            [
                'title' => 'Inspeksi Peralatan Kerja',
                'category' => 'Leading',
                'unit' => 'Dieng Unit 1',
                'year' => 2025,
                'target' => 12,
                'realization' => 8,
                'unit_measure' => 'kali/tahun',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'status' => 'Berlangsung',
                'description' => 'Pemeriksaan rutin kelayakan alat berat, perancah, dan perkakas listrik.',
                'notes' => [
                    ['note' => 'Tagging warna inspeksi triwulan berjalan sudah diperbarui.', 'author' => 'Dwi Prasetyo'],
                ],
            ],
            [
                'title' => 'Pelatihan K3 Khusus',
                'category' => 'Leading',
                'unit' => 'Dieng Unit 1',
                'year' => 2025,
                'target' => 4,
                'realization' => 4,
                'unit_measure' => 'sesi/tahun',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'status' => 'Tercapai',
                'description' => 'Target 4 sesi pelatihan sertifikasi dan awareness K3 tingkat lanjut.',
                'notes' => [
                    ['note' => 'Sesi 4 pelatihan bekerja di ketinggian telah selesai dilaksanakan.', 'author' => 'Siti Rahma'],
                ],
            ],
            [
                'title' => 'Simulasi Emergency',
                'category' => 'Leading',
                'unit' => 'Dieng Unit 1',
                'year' => 2025,
                'target' => 2,
                'realization' => 0,
                'unit_measure' => 'kali/tahun',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'status' => 'Belum Mulai',
                'description' => 'Jadwal drill tanggap darurat bahaya gas H2S dan gempa bumi semester 2.',
                'notes' => [
                    ['note' => 'Penyusunan skenario drill sedang dikoordinasikan dengan tim ERT Dieng.', 'author' => 'Budi Santoso'],
                ],
            ],
            [
                'title' => 'TRIR (Total Recordable Incident Rate)',
                'category' => 'Lagging',
                'unit' => 'Dieng Unit 1',
                'year' => 2025,
                'target' => 0.5,
                'realization' => 0.3,
                'unit_measure' => 'index',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'status' => 'Tercapai',
                'description' => 'Tingkat frekuensi total insiden tercatat per 200.000 jam kerja.',
                'notes' => [
                    ['note' => 'Target indeks tercapai di bawah batas toleransi maksimal 0.5.', 'author' => 'Ahmad Fauzi'],
                ],
            ],
            [
                'title' => 'LTIFR (Lost Time Injury Frequency Rate)',
                'category' => 'Lagging',
                'unit' => 'Dieng Unit 1',
                'year' => 2025,
                'target' => 0,
                'realization' => 0,
                'unit_measure' => 'index',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'status' => 'Tercapai',
                'description' => 'Zero Lost Time Injury target untuk seluruh kegiatan operasional.',
                'notes' => [
                    ['note' => 'Mempertahankan nihil hari kerja hilang hingga triwulan berjalan.', 'author' => 'Siti Rahma'],
                ],
            ],
            [
                'title' => 'First Aid Rate',
                'category' => 'Lagging',
                'unit' => 'Dieng Unit 1',
                'year' => 2025,
                'target' => 1,
                'realization' => 2,
                'unit_measure' => 'kasus',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'status' => 'Tidak Tercapai',
                'description' => 'Jumlah insiden pertolongan pertama (P3K) ringan di lokasi kerja.',
                'notes' => [
                    ['note' => 'Perlu evaluasi kotak P3K dan penyegaran penanganan luka ringan di lapangan.', 'author' => 'Dwi Prasetyo'],
                ],
            ],
            [
                'title' => 'Perilaku Aman (BBS)',
                'category' => 'Lagging',
                'unit' => 'Dieng Unit 1',
                'year' => 2025,
                'target' => 80,
                'realization' => 70,
                'unit_measure' => '%',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'status' => 'Berlangsung',
                'description' => 'Tingkat kepatuhan observasi Behavior Based Safety di area kerja.',
                'notes' => [
                    ['note' => 'Mendorong peningkatan partisipasi observasi kartu aman antar rekan kerja.', 'author' => 'Budi Santoso'],
                ],
            ],
            [
                'title' => 'Laporan Near Miss',
                'category' => 'Lagging',
                'unit' => 'Dieng Unit 1',
                'year' => 2025,
                'target' => 4,
                'realization' => 4,
                'unit_measure' => 'laporan/tahun',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'status' => 'Tercapai',
                'description' => 'Rekapitulasi pelaporan bahaya dan kejadian hampir celaka.',
                'notes' => [
                    ['note' => 'Seluruh laporan near miss telah diverifikasi dan tindak lanjut ditutup.', 'author' => 'Ahmad Fauzi'],
                ],
            ],
            [
                'title' => 'Audit Kepatuhan HSSE',
                'category' => 'Lagging',
                'unit' => 'Dieng Unit 1',
                'year' => 2025,
                'target' => 2,
                'realization' => 2,
                'unit_measure' => 'kali/tahun',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'status' => 'Tercapai',
                'description' => 'Audit kepatuhan sistem manajemen keselamatan kontraktor.',
                'notes' => [
                    ['note' => 'Hasil audit siklus 1 dan 2 telah dipresentasikan kepada manajemen.', 'author' => 'Siti Rahma'],
                ],
            ],
        ];

        foreach ($kpiItems as $item) {
            $notes = $item['notes'] ?? [];
            unset($item['notes']);

            $plan = HssePlan::create($item);

            foreach ($notes as $noteData) {
                HssePlanNote::create([
                    'hsse_plan_id' => $plan->id,
                    'note' => $noteData['note'],
                    'author' => $noteData['author'] ?? 'Admin HSSE',
                ]);
            }
        }
    }
}

