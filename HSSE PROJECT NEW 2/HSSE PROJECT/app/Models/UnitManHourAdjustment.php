<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitManHourAdjustment extends Model
{
    use HasFactory;

    protected $table = 'unit_man_hour_adjustments';

    protected $fillable = [
        'operational_unit_id',
        'unit_name',
        'previous_accumulated_hours',
        'new_accumulated_hours',
        'adjustment_delta',
        'notes',
        'adjusted_by',
    ];

    protected $casts = [
        'previous_accumulated_hours' => 'float',
        'new_accumulated_hours' => 'float',
        'adjustment_delta' => 'float',
    ];

    public function operationalUnit(): BelongsTo
    {
        return $this->belongsTo(OperationalUnit::class, 'operational_unit_id');
    }
}

