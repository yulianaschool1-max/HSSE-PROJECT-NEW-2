<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contractor extends Model
{
    use HasFactory;

    protected $table = 'contractors';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke HSSE Plan
     */
    public function hssePlans(): HasMany
    {
        return $this->hasMany(HssePlan::class, 'contractor_id');
    }

    /**
     * Pastikan kontraktor default terdaftar di database jika tabel kosong.
     */
    public static function ensureDefaultsSeeded(): void
    {
        if (self::count() === 0) {
            $defaults = [
                ['name' => 'PT Geo Dipa Energi (Persero)', 'code' => 'GDE', 'description' => 'Owner / Unit Pelaksana Utama Geodipa'],
                ['name' => 'PT Rekayasa Industri', 'code' => 'REKIND', 'description' => 'Main EPC Contractor Proyek Geotermal'],
                ['name' => 'PT PLN Indonesia Power', 'code' => 'PLN-IP', 'description' => 'Mitra Pembangkitan & Jaringan Listrik'],
                ['name' => 'PT Pertamina Geothermal Energy', 'code' => 'PGE', 'description' => 'Mitra Pengembang Panas Bumi'],
                ['name' => 'PT Halliburton Indonesia', 'code' => 'HLB', 'description' => 'Kontraktor Pengeboran & Sumur'],
                ['name' => 'PT Schlumberger Geophysics Nusantara', 'code' => 'SLB', 'description' => 'Kontraktor Pengukuran Geofisika & Logging'],
                ['name' => 'PT Elnusa Tbk', 'code' => 'ELNUSA', 'description' => 'Kontraktor Jasa Energi & Rig Services'],
            ];

            foreach ($defaults as $item) {
                self::firstOrCreate(['name' => $item['name']], [
                    'code' => $item['code'],
                    'description' => $item['description'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
