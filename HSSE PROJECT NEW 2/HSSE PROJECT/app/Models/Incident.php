<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Incident extends Model
{
    use HasFactory;

    protected $table = 'incidents';

    public const KLASIFIKASI_AREAS = [
        'Kecelakaan Panas Bumi',
        'Kecelakaan Non-Panas Bumi',
    ];

    public const KATEGORI_UMUM_LIST = [
        'Fatality',
        'LTI (Lost Time Injury)',
        'Medical Treatment Case',
        'First Aid Case',
        'Property Damage',
        'Near Miss (Hampir Celaka)',
        'Environmental Incident',
        'Vehicle Accident',
        'Fire Incident',
        'Hazard Report',
    ];

    public const KATEGORI_EBTKE_LIST = [
        'Kecelakaan Mati',
        'Kecelakaan Berat',
        'Kecelakaan Ringan',
        'Kejadian Berbahaya',
        'Penyakit Akibat Kerja',
    ];

    protected $fillable = [
        'incident_code',
        'nomor_insiden',
        'title',
        'incident_type',
        'unit',
        'incident_date',
        'tanggal_waktu',
        'location',
        'lokasi_kejadian',
        'severity',
        'description',
        'deskripsi_kejadian',
        'immediate_action',
        'root_cause',
        'corrective_action',
        'tindakan_perbaikan',
        'status',
        'status_laporan',
        'reported_by',
        'penanggung_jawab',
        'target_penyelesaian',
        'closed_at',
        'klasifikasi_area',
        'kategori_umum',
        'kategori_ebtke',
        'komponen_terkait',
        'jumlah_hari_hilang',
        'dokumen_investigasi',
    ];

    protected $casts = [
        'incident_date' => 'datetime',
        'tanggal_waktu' => 'datetime',
        'target_penyelesaian' => 'date',
        'closed_at' => 'date',
        'kategori_umum' => 'array',
        'kategori_ebtke' => 'array',
        'jumlah_hari_hilang' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Incident $inc) {
            // Sinkronisasi nomor_insiden <-> incident_code
            if (empty($inc->nomor_insiden) && !empty($inc->incident_code)) {
                $inc->nomor_insiden = $inc->incident_code;
            } elseif (empty($inc->incident_code) && !empty($inc->nomor_insiden)) {
                $inc->incident_code = $inc->nomor_insiden;
            } elseif (empty($inc->nomor_insiden) && empty($inc->incident_code)) {
                $code = 'INC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
                $inc->nomor_insiden = $code;
                $inc->incident_code = $code;
            }

            // Sinkronisasi tanggal_waktu <-> incident_date
            if (empty($inc->tanggal_waktu) && !empty($inc->incident_date)) {
                $inc->tanggal_waktu = $inc->incident_date;
            } elseif (empty($inc->incident_date) && !empty($inc->tanggal_waktu)) {
                $inc->incident_date = $inc->tanggal_waktu;
            } elseif (empty($inc->tanggal_waktu) && empty($inc->incident_date)) {
                $now = now();
                $inc->tanggal_waktu = $now;
                $inc->incident_date = $now;
            }

            // Sinkronisasi lokasi
            if (empty($inc->lokasi_kejadian) && !empty($inc->location)) {
                $inc->lokasi_kejadian = $inc->location;
            } elseif (empty($inc->location) && !empty($inc->lokasi_kejadian)) {
                $inc->location = $inc->lokasi_kejadian;
            } elseif (empty($inc->lokasi_kejadian) && empty($inc->location)) {
                $inc->lokasi_kejadian = 'Area Operasi Dieng';
                $inc->location = 'Area Operasi Dieng';
            }

            // Sinkronisasi unit / komponen
            if (empty($inc->komponen_terkait) && !empty($inc->unit)) {
                $inc->komponen_terkait = $inc->unit;
            } elseif (empty($inc->unit) && !empty($inc->komponen_terkait)) {
                $inc->unit = $inc->komponen_terkait;
            } elseif (empty($inc->komponen_terkait) && empty($inc->unit)) {
                $inc->komponen_terkait = 'Unit Dieng';
                $inc->unit = 'Unit Dieng';
            }

            // Sinkronisasi deskripsi
            if (empty($inc->deskripsi_kejadian) && !empty($inc->description)) {
                $inc->deskripsi_kejadian = $inc->description;
            } elseif (empty($inc->description) && !empty($inc->deskripsi_kejadian)) {
                $inc->description = $inc->deskripsi_kejadian;
            } elseif (empty($inc->deskripsi_kejadian) && empty($inc->description)) {
                $inc->deskripsi_kejadian = '-';
                $inc->description = '-';
            }

            // Sinkronisasi penanggung jawab
            if (empty($inc->penanggung_jawab) && !empty($inc->reported_by)) {
                $inc->penanggung_jawab = $inc->reported_by;
            } elseif (empty($inc->reported_by) && !empty($inc->penanggung_jawab)) {
                $inc->reported_by = $inc->penanggung_jawab;
            } elseif (empty($inc->penanggung_jawab) && empty($inc->reported_by)) {
                $defaultUser = auth()->user()?->name ?? 'Petugas HSSE';
                $inc->penanggung_jawab = $defaultUser;
                $inc->reported_by = $defaultUser;
            }

            // Sinkronisasi tindakan perbaikan
            if (empty($inc->tindakan_perbaikan) && !empty($inc->corrective_action)) {
                $inc->tindakan_perbaikan = $inc->corrective_action;
            } elseif (empty($inc->corrective_action) && !empty($inc->tindakan_perbaikan)) {
                $inc->corrective_action = $inc->tindakan_perbaikan;
            }

            // Status & status_laporan
            if (empty($inc->status_laporan)) {
                $inc->status_laporan = ($inc->status === 'Selesai') ? 'Final' : 'Preliminary';
            }
            if (empty($inc->status) || !in_array($inc->status, ['Dilaporkan', 'Investigasi', 'Dalam Penanganan', 'Selesai'])) {
                $inc->status = ($inc->status_laporan === 'Final') ? 'Selesai' : 'Dilaporkan';
            }

            // Severity & jumlah_hari_hilang
            if (empty($inc->severity)) {
                $inc->severity = 'Low';
            }
            if ($inc->jumlah_hari_hilang === null) {
                $inc->jumlah_hari_hilang = 0;
            }

            // Default title & incident_type
            if (empty($inc->title)) {
                $inc->title = $inc->nomor_insiden . ' - ' . ($inc->lokasi_kejadian ?? 'Dieng');
            }
            if (empty($inc->incident_type)) {
                $inc->incident_type = $inc->klasifikasi_area ?? 'Kecelakaan Panas Bumi';
            }
        });
    }

    /**
     * Generate Next Incident Number sequentially.
     */
    public static function generateNextNumber(): string
    {
        $year = date('Y');
        $prefix = "INC-GDE-{$year}-";
        $latest = static::where('nomor_insiden', 'like', "{$prefix}%")
            ->orWhere('incident_code', 'like', "{$prefix}%")
            ->latest('id')
            ->first();

        if ($latest) {
            $num = $latest->nomor_insiden ?? $latest->incident_code;
            if (preg_match('/(\d+)$/', $num, $matches)) {
                $next = (int) $matches[1] + 1;
                return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
            }
        }

        return $prefix . '001';
    }
}
