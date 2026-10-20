<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HssePlanNote extends Model
{
    use HasFactory;

    protected $table = 'hsse_plan_notes';

    protected $fillable = [
        'hsse_plan_id',
        'note',
        'author',
    ];

    /**
     * Relationship to the parent HSSE Plan / KPI.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(HssePlan::class, 'hsse_plan_id');
    }
}

