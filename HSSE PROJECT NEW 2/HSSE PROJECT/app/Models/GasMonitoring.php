<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GasMonitoring extends Model
{
    use HasFactory;

    protected $table = 'gas_monitorings';

    // NAB / Threshold Config
    public const NAB_CO = 25.0;            // ppm (max)
    public const NAB_O2_MIN = 19.5;        // %
    public const NAB_O2_MAX = 23.5;        // %
    public const NAB_H2S = 10.0;           // ppm (max)
    public const NAB_LEL = 10.0;           // %LEL (max)
    public const NAB_PRESSURE = 20.0;      // barg (max)

    protected $fillable = [
        'measurement_date',
        'well_pad_id',
        'well_pad',
        'co_level',
        'o2_level',
        'h2s_level',
        'lel_level',
        'pressure',
        'co_status',
        'o2_status',
        'h2s_status',
        'lel_status',
        'pressure_status',
        'overall_status',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'measurement_date' => 'date:Y-m-d',
            'co_level' => 'float',
            'o2_level' => 'float',
            'h2s_level' => 'float',
            'lel_level' => 'float',
            'pressure' => 'float',
        ];
    }

    public function wellPad(): BelongsTo
    {
        return $this->belongsTo(WellPad::class, 'well_pad_id');
    }

    protected static function booted(): void
    {
        static::saving(function (GasMonitoring $item) {
            $item->co_level = $item->co_level !== null ? (float) $item->co_level : null;
            $item->o2_level = $item->o2_level !== null ? (float) $item->o2_level : null;
            $item->h2s_level = $item->h2s_level !== null ? (float) $item->h2s_level : null;
            $item->lel_level = $item->lel_level !== null ? (float) $item->lel_level : null;
            $item->pressure = $item->pressure !== null ? (float) $item->pressure : null;

            // Status calculations based on threshold
            $item->co_status = ($item->co_level !== null && $item->co_level > self::NAB_CO) ? 'Tidak Sesuai' : 'Sesuai';
            
            if ($item->o2_level !== null) {
                $item->o2_status = ($item->o2_level < self::NAB_O2_MIN || $item->o2_level > self::NAB_O2_MAX) ? 'Tidak Sesuai' : 'Sesuai';
            } else {
                $item->o2_status = 'Sesuai';
            }

            $item->h2s_status = ($item->h2s_level !== null && $item->h2s_level > self::NAB_H2S) ? 'Tidak Sesuai' : 'Sesuai';
            $item->lel_status = ($item->lel_level !== null && $item->lel_level > self::NAB_LEL) ? 'Tidak Sesuai' : 'Sesuai';
            $item->pressure_status = ($item->pressure !== null && $item->pressure > self::NAB_PRESSURE) ? 'Tidak Sesuai' : 'Sesuai';

            $isAllConforming = ($item->co_status === 'Sesuai')
                && ($item->o2_status === 'Sesuai')
                && ($item->h2s_status === 'Sesuai')
                && ($item->lel_status === 'Sesuai')
                && ($item->pressure_status === 'Sesuai');

            $item->overall_status = $isAllConforming ? 'Sesuai' : 'Tidak Sesuai';
        });
    }

    public function scopeByDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('measurement_date', $date);
    }

    public function scopeByMonthYear(Builder $query, int $month, int $year): Builder
    {
        return $query->whereMonth('measurement_date', $month)
            ->whereYear('measurement_date', $year);
    }

    public function scopeByWellPad(Builder $query, ?string $wellPad): Builder
    {
        if (!empty($wellPad) && $wellPad !== 'all') {
            return $query->where('well_pad', $wellPad);
        }
        return $query;
    }
}
