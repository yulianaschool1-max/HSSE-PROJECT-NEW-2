<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssueFollowUp extends Model
{
    use HasFactory;

    protected $table = 'issue_follow_ups';

    public const CATEGORIES = [
        'Safety Inspection',
        'Kondisi Tidak Aman (Unsafe Condition)',
        'Tindakan Tidak Aman (Unsafe Action)',
        'Housekeeping & 5R',
        'APD & Peralatan Keselamatan',
        'Lingkungan & Limbah B3',
        'Peralatan & Utilitas',
        'Keamanan (Security)',
    ];

    public const UNITS = [
        'PMU',
        'Dieng Unit 1',
        'Dieng Unit 2',
        'Utility',
        'JOTRE',
        'BISMO',
    ];

    public const PRIORITIES = [
        'Rendah',
        'Sedang',
        'Tinggi',
        'Kritis',
    ];

    public const STATUSES = [
        'Open',
        'In Progress',
        'Closed',
    ];

    public const HSSE_DIVISIONS = [
        'H' => 'H — Health & Savety',
        'S_SOCIAL' => 'S — Sosial',
        'S_SECURITY' => 'S — Scurity',
        'E' => 'E — Emvironment',
    ];

    public const ACTIVE_STATUSES = [
        'Open',
        'In Progress',
    ];

    public const CLOSED_STATUSES = [
        'Closed',
        'Close',
    ];

    protected $fillable = [
        'issue_code',
        'hsse_division',
        'title',
        'category',
        'unit',
        'reported_date',
        'due_date',
        'location',
        'description',
        'action_plan',
        'pic',
        'priority',
        'status',
        'closed_date',
        'closed_by',
        'notes',
        'attachment_path',
    ];

    protected $appends = [
        'is_overdue',
        'status_badge_class',
        'priority_badge_class',
        'division_label',
        'division_badge_class',
    ];

    protected function casts(): array
    {
        return [
            'reported_date' => 'date:Y-m-d',
            'due_date' => 'date:Y-m-d',
            'closed_date' => 'date:Y-m-d',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (IssueFollowUp $issue) {
            if (empty($issue->issue_code)) {
                $datePart = $issue->reported_date
                    ? Carbon::parse($issue->reported_date)->format('Ymd')
                    : date('Ymd');
                $randomPart = strtoupper(substr(uniqid(), -4));
                $candidate = "IFU-{$datePart}-{$randomPart}";
                while (static::where('issue_code', $candidate)->exists()) {
                    $candidate = "IFU-{$datePart}-" . random_int(1000, 9999);
                }
                $issue->issue_code = $candidate;
            }

            if (empty($issue->unit)) {
                $issue->unit = 'PMU';
            }

            if (empty($issue->category)) {
                $issue->category = 'Safety Inspection';
            }

            if (empty($issue->priority)) {
                $issue->priority = 'Sedang';
            }

            // Normalisasi nilai status agar konsisten ('Open', 'In Progress', 'Closed')
            if (strcasecmp((string) $issue->status, 'Close') === 0 || strcasecmp((string) $issue->status, 'Closed') === 0) {
                $issue->status = 'Closed';
            } elseif (strcasecmp((string) $issue->status, 'In Progress') === 0) {
                $issue->status = 'In Progress';
            } else {
                $issue->status = 'Open';
            }

            if ($issue->status === 'Closed' && empty($issue->closed_date)) {
                $issue->closed_date = Carbon::today()->format('Y-m-d');
            }
        });
    }

    /**
     * Relasi riwayat tindak lanjut (kronologis terbaru di atas).
     */
    public function logs(): HasMany
    {
        return $this->hasMany(IssueFollowUpLog::class, 'issue_follow_up_id')
            ->orderBy('followed_up_at', 'desc')
            ->orderBy('id', 'desc');
    }

    /**
     * Relasi riwayat tindak lanjut secara kronologis dari awal ke akhir.
     */
    public function chronologicalLogs(): HasMany
    {
        return $this->hasMany(IssueFollowUpLog::class, 'issue_follow_up_id')
            ->orderBy('followed_up_at', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * Apakah temuan melewati tenggat dan belum berstatus Closed.
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'Closed' || empty($this->due_date)) {
            return false;
        }

        return Carbon::parse($this->due_date)->startOfDay()->lt(Carbon::today());
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->isOverdue();
    }

    public function getDateFoundAttribute(): mixed
    {
        return $this->reported_date;
    }

    public function getResponsiblePersonAttribute(): ?string
    {
        return $this->pic;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->status === 'Closed') {
            return 'status-closed';
        }
        if ($this->status === 'In Progress') {
            return 'status-in-progress';
        }
        return 'status-open';
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'Kritis' => 'priority-critical',
            'Tinggi' => 'priority-high',
            'Rendah' => 'priority-low',
            default => 'priority-medium',
        };
    }

    public function getDivisionLabelAttribute(): string
    {
        $code = strtoupper(trim((string) $this->hsse_division));
        if ($code === 'S' || $code === 'S_SOCIAL' || $code === 'SOSIAL' || $code === 'SOCIAL') {
            return self::HSSE_DIVISIONS['S_SOCIAL'];
        }
        if ($code === 'S_SECURITY' || $code === 'SCURITY' || $code === 'SECURITY') {
            return self::HSSE_DIVISIONS['S_SECURITY'];
        }
        return self::HSSE_DIVISIONS[$code] ?? 'Belum Diklasifikasikan';
    }

    public function getDivisionBadgeClassAttribute(): string
    {
        $code = strtoupper(trim((string) $this->hsse_division));
        return match ($code) {
            'H' => 'division-h',
            'S', 'S_SOCIAL', 'SOSIAL' => 'division-s-social',
            'S_SECURITY', 'SCURITY', 'SECURITY' => 'division-s-security',
            'E' => 'division-e',
            default => 'division-unclassified',
        };
    }

    /**
     * Scope filter berdasarkan Divisi HSSE (H, S_SOCIAL, S_SECURITY, E, atau unclassified).
     */
    public function scopeByDivision(Builder $query, ?string $division): Builder
    {
        if (empty($division)) {
            return $query;
        }

        $normalized = strtoupper(trim($division));
        if ($normalized === 'UNCLASSIFIED' || $normalized === 'BELUM DIKLASIFIKASIKAN') {
            return $query->where(function (Builder $q) {
                $q->whereNull('hsse_division')->orWhere('hsse_division', '');
            });
        }

        if ($normalized === 'S_SOCIAL' || $normalized === 'S' || $normalized === 'SOSIAL' || $normalized === 'SOCIAL') {
            return $query->where(function (Builder $q) {
                $q->where('hsse_division', 'S_SOCIAL')
                  ->orWhere('hsse_division', 'S');
            });
        }

        if ($normalized === 'S_SECURITY' || $normalized === 'SCURITY' || $normalized === 'SECURITY') {
            return $query->where(function (Builder $q) {
                $q->where('hsse_division', 'S_SECURITY')
                  ->orWhere('hsse_division', 'SCURITY')
                  ->orWhere('hsse_division', 'SECURITY');
            });
        }

        if (array_key_exists($normalized, self::HSSE_DIVISIONS)) {
            return $query->where('hsse_division', $normalized);
        }

        return $query;
    }

    /**
     * Scope untuk temuan Overdue (due_date < hari ini dan status != Closed).
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', 'Closed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', Carbon::today()->toDateString());
    }

    /**
     * Scope query backend untuk bagian "Highlight Temuan Terbaru" (Temuan Aktif):
     * Hanya mengambil temuan yang masih memerlukan penanganan ('Open' dan 'In Progress').
     * Temuan berstatus 'Closed' otomatis dikeluarkan dari highlight.
     * Diurutkan berdasarkan tanggal temuan terbaru (reported_date DESC), waktu pembuatan, dan ID.
     */
    public function scopeActiveForHighlight(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES)
            ->whereNotIn('status', self::CLOSED_STATUSES)
            ->orderBy('reported_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');
    }
}
