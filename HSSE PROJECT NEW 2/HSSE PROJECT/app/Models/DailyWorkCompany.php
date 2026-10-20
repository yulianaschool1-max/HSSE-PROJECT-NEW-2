<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyWorkCompany extends Model
{
    use HasFactory;

    protected $table = 'daily_work_companies';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope untuk perusahaan yang berstatus aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

