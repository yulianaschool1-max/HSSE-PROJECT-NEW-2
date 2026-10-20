<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManHour extends Model
{
    use HasFactory;

    protected $table = 'man_hours';

    protected $fillable = [
        'workforce_id',
        'operational_unit_id',
        'category',
        'unit',
        'work_date',
        'number_of_workers',
        'hours_per_worker',
        'total_hours',
        'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'number_of_workers' => 'integer',
        'hours_per_worker' => 'float',
        'total_hours' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $workers = max(0, (int) ($model->number_of_workers ?? 0));
            $hours = max(0, (float) ($model->hours_per_worker ?? Workforce::DEFAULT_HOURS_PER_WORKER));

            $model->number_of_workers = $workers;
            $model->hours_per_worker = $hours;
            $model->total_hours = round($workers * $hours, 2);

            if (empty($model->unit)) {
                $model->unit = 'PMU';
            }
            if (empty($model->category)) {
                $model->category = $model->unit ?: 'GDE';
            }
            if (empty($model->operational_unit_id) && !empty($model->unit)) {
                $unitId = OperationalUnit::where('name', $model->unit)->value('id');
                if ($unitId) {
                    $model->operational_unit_id = $unitId;
                }
            }
        });
    }

    /**
     * Relasi ke sumber data jumlah pekerja (Workforce).
     */
    public function workforce(): BelongsTo
    {
        return $this->belongsTo(Workforce::class, 'workforce_id');
    }

    /**
     * Relasi ke master Unit / Area Operasi.
     */
    public function operationalUnit(): BelongsTo
    {
        return $this->belongsTo(OperationalUnit::class, 'operational_unit_id');
    }

    /**
     * Pastikan seluruh data Workforce yang belum memiliki baris ManHour tersinkronisasi
     * secara idempoten (tidak ada duplikasi atau perhitungan ganda).
     */
    public static function syncAllFromWorkforce(): void
    {
        $unlinkedWorkforces = Workforce::whereDoesntHave('manHour')->get();

        foreach ($unlinkedWorkforces as $workforce) {
            $workforce->syncManHour();
        }
    }
}

