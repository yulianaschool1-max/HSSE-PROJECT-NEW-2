<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    public const STATUS_SELESAI = 'Selesai';

    public const STATUSES = [
        'Selesai',
        'Berlangsung',
        'Direncanakan',
        'Dibatalkan',
    ];

    protected $fillable = [
        'title',
        'category',
        'unit',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'participants',
        'pic',
        'status',
        'description',
        'documentation',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'participants' => 'integer',
        ];
    }

    /**
     * Scope khusus kegiatan yang telah terlaksana / selesai.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    /**
     * Scope pengurutan kegiatan dari tanggal terbaru ke terlama.
     */
    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->orderBy('event_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'record_id')->where('module', 'events');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'record_id')
            ->where('module', 'events')
            ->latest('id');
    }

    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            'Pelatihan' => '#fbe8ea',
            'Safety Talk' => '#fef0e3',
            'Inspeksi' => '#e8f4fd',
            'Simulasi/Drill' => '#fde8eb',
            'Kampanye' => '#eef5ec',
            'Sosialisasi' => '#e7f7f0',
            default => '#ede9fe',
        };
    }

    public function getCategoryTextColorAttribute(): string
    {
        return match ($this->category) {
            'Pelatihan' => '#b83250',
            'Safety Talk' => '#b45309',
            'Inspeksi' => '#1d4ed8',
            'Simulasi/Drill' => '#dc2626',
            'Kampanye' => '#15803d',
            'Sosialisasi' => '#047857',
            default => '#6b21a8',
        };
    }
}

