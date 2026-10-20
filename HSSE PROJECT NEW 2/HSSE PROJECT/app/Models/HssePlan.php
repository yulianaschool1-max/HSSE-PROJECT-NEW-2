<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class HssePlan extends Model
{
    use HasFactory;

    protected $table = 'hsse_plans';

    protected $fillable = [
        'title',
        'name',
        'category',
        'type',
        'unit',
        'year',
        'period',
        'target',
        'realization',
        'actual',
        'unit_measure',
        'start_date',
        'end_date',
        'status',
        'contractor_id',
        'contractor_name',
        'description',
    ];

    protected $casts = [
        'target' => 'float',
        'realization' => 'float',
        'year' => 'integer',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    protected $appends = [
        'name',
        'type',
        'actual',
        'period',
        'target_label',
        'percentage',
    ];

    /**
     * Relationship to Notes.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(HssePlanNote::class, 'hsse_plan_id')->orderBy('created_at', 'desc');
    }

    /**
     * Relationship to Contractor.
     */
    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class, 'contractor_id');
    }

    /**
     * Mutator & Accessor for title <-> name alias.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->title,
            set: fn ($value) => ['title' => $value],
        );
    }

    /**
     * Mutator & Accessor for category <-> type alias.
     */
    protected function type(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->category,
            set: fn ($value) => ['category' => $value],
        );
    }

    /**
     * Mutator & Accessor for realization <-> actual alias.
     */
    protected function actual(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) ($this->realization ?? 0),
            set: fn ($value) => ['realization' => $value],
        );
    }

    /**
     * Mutator & Accessor for year <-> period alias.
     */
    protected function period(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) ($this->year ?? date('Y')),
            set: fn ($value) => ['year' => $value],
        );
    }

    /**
     * Formatted target label attribute.
     */
    public function getTargetLabelAttribute(): string
    {
        $target = (float) ($this->target ?? 0);
        $measure = trim($this->unit_measure ?? '');

        if ($measure === '%') {
            return (rtrim(rtrim(number_format($target, 2, '.', ''), '0'), '.') ?: '0') . '%';
        }

        if ($measure) {
            return (rtrim(rtrim(number_format($target, 2, '.', ''), '0'), '.') ?: '0') . ' ' . $measure;
        }

        return (string) $target;
    }

    /**
     * Computed percentage attribute.
     */
    public function getPercentageAttribute(): int
    {
        $target = (float) ($this->target ?? 0);
        $actual = (float) ($this->realization ?? 0);

        if ($target <= 0) {
            return $actual <= 0 ? 100 : 0;
        }

        return (int) max(0, min(100, round(($actual / $target) * 100)));
    }
}
