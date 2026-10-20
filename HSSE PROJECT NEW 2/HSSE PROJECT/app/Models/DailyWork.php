<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DailyWork extends Model
{
    public const STATUS_NOT_STARTED = 'Belum Mulai';
    public const STATUS_ONGOING = 'Berlangsung';
    public const STATUS_COMPLETED = 'Selesai';
    public const STATUS_PAUSED = 'Ditunda';

    public const STATUSES = [
        self::STATUS_NOT_STARTED,
        self::STATUS_ONGOING,
        self::STATUS_COMPLETED,
        self::STATUS_PAUSED,
    ];

    public const UNIT_GDE = 'GDE (Internal)';
    public const UNIT_BUJP = 'BUJP (Security)';
    public const UNIT_PJSE = 'PJSE (Utility)';
    public const UNIT_JOTRE = 'JOTRE';
    public const UNIT_PMC = 'PMC';

    public const UNITS = [
        self::UNIT_GDE,
        self::UNIT_BUJP,
        self::UNIT_PJSE,
        self::UNIT_JOTRE,
        self::UNIT_PMC,
    ];

    public const UNITS_EXTERNAL = [
        self::UNIT_BUJP,
        self::UNIT_PJSE,
        self::UNIT_JOTRE,
        self::UNIT_PMC,
    ];

    protected $fillable = [
        'name',
        'unit',
        'category',
        'ptw_classification',
        'sikka',
        'start_date',
        'end_date',
        'location',
        'executor',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function getCompanyAttribute(): ?string
    {
        return $this->unit;
    }

    public function setCompanyAttribute(?string $value): void
    {
        $this->unit = $value;
    }

    protected static function booted(): void
    {
        static::saving(function (DailyWork $work) {
            // Default nilai category jika tidak dikirim dari form baru
            if (empty($work->category)) {
                $work->category = 'Umum';
            }

            // Sinkronkan executor dengan ptw_classification bila tidak diisi
            if (empty($work->executor)) {
                $work->executor = $work->ptw_classification ?: '-';
            }

            // Otomatis tentukan status secara aman tanpa input manual dari form
            if (empty($work->status)) {
                $today = \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
                $end = $work->end_date ? $work->end_date->format('Y-m-d') : $today;
                $work->status = ($end < $today) ? self::STATUS_COMPLETED : self::STATUS_ONGOING;
            } else {
                $work->status = self::normalizeStatus($work->status);
            }

            $work->unit = self::normalizeUnit($work->unit);
        });
    }

    /**
     * Normalisasi nilai unit agar pilihan unit baru tersimpan dengan konsisten,
     * serta mendukung pemetaan unit lama secara aman.
     */
    public static function normalizeUnit(?string $unit): string
    {
        $raw = trim((string) $unit);
        if ($raw === '') {
            return self::UNIT_GDE;
        }

        return match (strtoupper($raw)) {
            'PMU', 'GDE', 'INTERNAL', 'GDE (INTERNAL)' => self::UNIT_GDE,
            'UTILITY', 'PJSE', 'PJSE (UTILITY)' => self::UNIT_PJSE,
            'BISMO', 'SECURITY', 'BUJP', 'BUJP (SECURITY)' => self::UNIT_BUJP,
            'JOTRE' => self::UNIT_JOTRE,
            'PMC' => self::UNIT_PMC,
            default => in_array($raw, self::UNITS, true) ? $raw : $raw,
        };
    }

    /**
     * Normalisasi nilai status agar kompatibel dengan enum database ('Belum Mulai', 'Berlangsung', 'Selesai', 'Ditunda')
     * maupun istilah umum ('In Progress', 'Completed', 'Cancelled', 'Dibatalkan').
     */
    public static function normalizeStatus(?string $status): string
    {
        $raw = trim((string) $status);
        if ($raw === '') {
            return self::STATUS_NOT_STARTED;
        }

        return match (strtolower($raw)) {
            'berlangsung', 'in progress', 'in_progress', 'ongoing', 'on going', 'aktif', 'active' => self::STATUS_ONGOING,
            'selesai', 'completed', 'complete', 'done', 'closed', 'close' => self::STATUS_COMPLETED,
            'ditunda', 'dibatalkan', 'batal', 'cancelled', 'canceled', 'paused', 'on hold' => self::STATUS_PAUSED,
            'belum mulai', 'not started', 'not_started', 'pending', 'planned' => self::STATUS_NOT_STARTED,
            default => in_array($raw, self::STATUSES, true) ? $raw : self::STATUS_NOT_STARTED,
        };
    }

    /**
     * Scope query untuk pekerjaan yang dimulai pada hari ini (berdasarkan zona waktu Asia/Jakarta).
     */
    public function scopeStartingToday(Builder $query): Builder
    {
        $today = \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
        return $query->whereDate('start_date', $today);
    }

    /**
     * Scope query untuk pekerjaan yang dimulai pada tanggal tertentu.
     */
    public function scopeStartingOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('start_date', $date);
    }

    /**
     * Scope query untuk pekerjaan aktif pada hari ini (berdasarkan zona waktu Asia/Jakarta).
     */
    public function scopeActiveToday(Builder $query): Builder
    {
        $today = \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
        return $this->scopeActiveOnDate($query, $today);
    }

    /**
     * Scope query untuk pekerjaan aktif pada tanggal tertentu (start_date <= date <= end_date).
     */
    public function scopeActiveOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('start_date', '<=', $date)
                     ->whereDate('end_date', '>=', $date)
                     ->orderBy('start_date', 'desc')
                     ->orderBy('id', 'desc');
    }

    /**
     * Scope query backend untuk highlight "Pekerjaan Sedang Berlangsung".
     */
    public function scopeOngoing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ONGOING)
            ->whereNotIn('status', [
                self::STATUS_COMPLETED,
                self::STATUS_PAUSED,
                self::STATUS_NOT_STARTED,
            ])
            ->orderBy('start_date', 'desc')
            ->orderBy('id', 'desc');
    }
}
