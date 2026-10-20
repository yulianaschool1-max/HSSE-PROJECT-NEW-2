<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Workforce extends Model
{
    use HasFactory;

    protected $table = 'workforces';

    /**
     * Kategori/komponen pekerja standar di lingkungan PT Geo Dipa Energi Unit Dieng.
     */
    public const CATEGORIES = [
        'GDE',
        'Kodipa',
        'Utility',
        'Security',
        'Contractor & Sub-Contractor',
        'Kerja Praktik',
    ];

    /**
     * Jam kerja standar per orang per hari (default 8 jam).
     */
    public const DEFAULT_HOURS_PER_WORKER = 8.00;

    protected $fillable = [
        'operational_unit_id',
        'record_date',
        'category',
        'worker_count',
        'hours_per_worker',
        'employee_code',
        'name',
        'unit',
        'department',
        'position',
        'employment_type',
        'join_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'record_date' => 'date',
        'join_date' => 'date',
        'worker_count' => 'integer',
        'hours_per_worker' => 'float',
    ];

    protected $appends = [
        'calculated_man_hours',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if (empty($model->record_date) && !empty($model->join_date)) {
                $model->record_date = $model->join_date;
            } elseif (!empty($model->record_date) && empty($model->join_date)) {
                $model->join_date = $model->record_date;
            } elseif (empty($model->record_date)) {
                $model->record_date = now()->toDateString();
                $model->join_date = $model->record_date;
            }

            if (!empty($model->operational_unit_id) && empty($model->unit)) {
                $unitName = OperationalUnit::where('id', $model->operational_unit_id)->value('name');
                if ($unitName) {
                    $model->unit = $unitName;
                }
            }

            if (empty($model->unit)) {
                $model->unit = 'PMU';
            }

            if (empty($model->operational_unit_id) && !empty($model->unit)) {
                $unitId = OperationalUnit::where('name', $model->unit)->value('id');
                if ($unitId) {
                    $model->operational_unit_id = $unitId;
                }
            }

            if (empty($model->category)) {
                $model->category = $model->unit ?: 'GDE';
            }

            $model->worker_count = max(0, (int) ($model->worker_count ?? 0));

            if ($model->hours_per_worker === null || $model->hours_per_worker === '') {
                $model->hours_per_worker = self::DEFAULT_HOURS_PER_WORKER;
            } else {
                $model->hours_per_worker = max(0, (float) $model->hours_per_worker);
            }

            if (empty($model->name)) {
                $model->name = $model->department ?: ($model->unit ?: $model->category);
            }

            if (empty($model->employee_code)) {
                $datePart = Carbon::parse($model->record_date)->format('Ymd');
                $model->employee_code = 'WF-' . $datePart . '-' . strtoupper(Str::random(6));
            }

            $validEmploymentTypes = ['Karyawan', 'Kontraktor', 'Vendor', 'Magang', 'Lainnya'];
            if (empty($model->employment_type) || !in_array($model->employment_type, $validEmploymentTypes, true)) {
                $model->employment_type = match ($model->category) {
                    'Contractor & Sub-Contractor' => 'Kontraktor',
                    'Security' => 'Vendor',
                    'Kerja Praktik' => 'Magang',
                    default => 'Karyawan',
                };
            }

            if (empty($model->status) || !in_array($model->status, ['Aktif', 'Tidak Aktif'], true)) {
                $model->status = 'Aktif';
            }
        });

        static::saved(function (self $model) {
            $model->syncManHour();
        });

        static::deleted(function (self $model) {
            ManHour::where('workforce_id', $model->id)->delete();
        });
    }

    /**
     * Relasi ke master Unit / Area Operasi.
     */
    public function operationalUnit(): BelongsTo
    {
        return $this->belongsTo(OperationalUnit::class, 'operational_unit_id');
    }

    /**
     * Relasi 1-ke-1 dengan rekap ManHour yang bersumber dari data Workforce ini.
     */
    public function manHour(): HasOne
    {
        return $this->hasOne(ManHour::class, 'workforce_id');
    }

    /**
     * Hitung nilai Man Hours = Jumlah Pekerja x Jam Kerja per Orang.
     */
    public function getCalculatedManHoursAttribute(): float
    {
        $workers = max(0, (int) $this->worker_count);
        $hours = max(0, (float) ($this->hours_per_worker ?? self::DEFAULT_HOURS_PER_WORKER));

        return round($workers * $hours, 2);
    }

    /**
     * Sinkronkan data Workforce ini ke tabel man_hours secara idempoten (tanpa duplikasi).
     */
    public function syncManHour(): ManHour
    {
        $workers = max(0, (int) $this->worker_count);
        $hoursPerWorker = max(0, (float) ($this->hours_per_worker ?? self::DEFAULT_HOURS_PER_WORKER));
        $totalHours = round($workers * $hoursPerWorker, 2);
        $workDate = $this->record_date
            ? Carbon::parse($this->record_date)->format('Y-m-d')
            : now()->format('Y-m-d');

        return ManHour::updateOrCreate(
            ['workforce_id' => $this->id],
            [
                'operational_unit_id' => $this->operational_unit_id,
                'category' => $this->category ?: ($this->unit ?: 'GDE'),
                'unit' => $this->unit ?: 'PMU',
                'work_date' => $workDate,
                'number_of_workers' => $workers,
                'hours_per_worker' => $hoursPerWorker,
                'total_hours' => $totalHours,
                'notes' => $this->notes,
            ]
        );
    }
}

