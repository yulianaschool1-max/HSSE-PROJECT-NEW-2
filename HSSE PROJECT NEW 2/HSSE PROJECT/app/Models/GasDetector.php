<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasDetector extends Model
{
    use HasFactory;

    protected $table = 'gas_detectors';

    protected $fillable = [
        'device_code',
        'model_name',
        'serial_number',
        'gas_type',
        'unit',
        'location',
        'status',
        'last_calibration_date',
        'next_calibration_date',
        'user_in_charge',
        'notes',
    ];

    protected $casts = [
        'last_calibration_date' => 'date',
        'next_calibration_date' => 'date',
    ];

    /**
     * Cek apakah perangkat sudah memasuki atau melewati tanggal kalibrasi.
     */
    public function isCalibrationDue(): bool
    {
        if (!$this->next_calibration_date) {
            return false;
        }

        return $this->next_calibration_date->isPast() || $this->next_calibration_date->isToday();
    }

    /**
     * Dapatkan kelas badge CSS untuk status perangkat.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Tersedia' => 'status-selesai',
            'Sedang Digunakan' => 'status-berlangsung',
            'Dalam Perawatan' => 'status-ditunda',
            'Perlu Kalibrasi' => 'status-belum',
            default => 'status-belum',
        };
    }
}

