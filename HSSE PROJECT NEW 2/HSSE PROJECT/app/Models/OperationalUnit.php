<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationalUnit extends Model
{
    use HasFactory;

    protected $table = 'operational_units';

    public const DEFAULT_UNITS = [
        ['name' => 'PMU', 'code' => 'UNIT-PMU', 'description' => 'Project Management Unit Dieng', 'sort_order' => 1],
        ['name' => 'Dieng Unit 1', 'code' => 'UNIT-D1', 'description' => 'PLTP Dieng Unit 1', 'sort_order' => 2],
        ['name' => 'Dieng Unit 2', 'code' => 'UNIT-D2', 'description' => 'PLTP Dieng Unit 2', 'sort_order' => 3],
        ['name' => 'Utility', 'code' => 'UNIT-UTL', 'description' => 'Area Utility & Fasilitas Penunjang', 'sort_order' => 4],
        ['name' => 'JOTRE', 'code' => 'UNIT-JTR', 'description' => 'Area Operasi JOTRE', 'sort_order' => 5],
        ['name' => 'BISMO', 'code' => 'UNIT-BSM', 'description' => 'Area Operasi BISMO', 'sort_order' => 6],
    ];

    protected $fillable = [
        'name',
        'code',
        'description',
        'default_hours',
        'manual_accumulated_hours',
        'accumulated_hours_adjustment',
        'accumulated_hours_baseline',
        'adjustment_notes',
        'last_adjusted_at',
        'adjusted_by',
        'is_active',
        'is_initial',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'default_hours' => 'float',
        'manual_accumulated_hours' => 'float',
        'accumulated_hours_adjustment' => 'float',
        'accumulated_hours_baseline' => 'float',
        'last_adjusted_at' => 'datetime',
        'is_active' => 'boolean',
        'is_initial' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');
    }

    public function workforces(): HasMany
    {
        return $this->hasMany(Workforce::class, 'operational_unit_id');
    }

    public function manHours(): HasMany
    {
        return $this->hasMany(ManHour::class, 'operational_unit_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(UnitManHourAdjustment::class, 'operational_unit_id');
    }

    /**
     * Pastikan master unit standar terisi jika tabel kosong.
     */
    public static function ensureDefaultsSeeded(): void
    {
        if (static::count() > 0) {
            return;
        }

        foreach (self::DEFAULT_UNITS as $u) {
            static::firstOrCreate(
                ['name' => $u['name']],
                [
                    'code' => $u['code'],
                    'description' => $u['description'],
                    'default_hours' => 8.00,
                    'is_active' => true,
                    'is_initial' => true,
                    'sort_order' => $u['sort_order'],
                    'created_by' => 'System Default',
                ]
            );
        }
    }
}

