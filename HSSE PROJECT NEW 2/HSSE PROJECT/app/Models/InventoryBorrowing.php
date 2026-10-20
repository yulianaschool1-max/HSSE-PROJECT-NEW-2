<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBorrowing extends Model
{
    use HasFactory;

    protected $table = 'inventory_borrowings';

    public const STATUS_BORROWED = 'Dipinjam';
    public const STATUS_PARTIAL = 'Sebagian Dikembalikan';
    public const STATUS_RETURNED = 'Dikembalikan';

    protected $fillable = [
        'borrow_code',
        'inventory_item_id',
        'item_id',
        'quantity_borrowed',
        'quantity_returned',
        'borrower_name',
        'department',
        'borrower_division',
        'borrow_date',
        'expected_return_date',
        'actual_return_date',
        'status',
        'purpose',
        'notes',
        'recorded_by',
        'user_id',
    ];

    public function setItemIdAttribute($value): void
    {
        $this->attributes['inventory_item_id'] = $value;
    }

    public function getItemIdAttribute()
    {
        return $this->attributes['inventory_item_id'] ?? null;
    }

    public function setBorrowerDivisionAttribute($value): void
    {
        $this->attributes['department'] = $value;
    }

    public function getBorrowerDivisionAttribute()
    {
        return $this->attributes['department'] ?? null;
    }

    protected $casts = [
        'borrow_date' => 'datetime',
        'expected_return_date' => 'date',
        'actual_return_date' => 'datetime',
        'quantity_borrowed' => 'integer',
        'quantity_returned' => 'integer',
    ];

    protected $appends = [
        'remaining_quantity',
        'is_overdue',
    ];

    protected static function booted(): void
    {
        static::creating(function (InventoryBorrowing $borrowing) {
            if (empty($borrowing->borrow_code)) {
                $borrowing->borrow_code = 'BRW-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            }
            if (empty($borrowing->status)) {
                $borrowing->status = self::STATUS_BORROWED;
            }
        });
    }

    /**
     * Sisa kuantitas yang masih dipinjam.
     */
    public function getRemainingQuantityAttribute(): int
    {
        return max(0, (int) $this->quantity_borrowed - (int) $this->quantity_returned);
    }

    /**
     * Cek apakah sudah melewati tanggal rencana pengembalian.
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === self::STATUS_RETURNED || $this->remaining_quantity <= 0) {
            return false;
        }

        if (!$this->expected_return_date) {
            return false;
        }

        return Carbon::today()->gt($this->expected_return_date);
    }

    /**
     * Scope peminjaman yang masih aktif (belum dikembalikan penuh).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_RETURNED)
                     ->whereRaw('quantity_borrowed > quantity_returned');
    }

    /**
     * Scope peminjaman yang sudah selesai/dikembalikan.
     */
    public function scopeReturned(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_RETURNED)
              ->orWhereRaw('quantity_borrowed <= quantity_returned');
        });
    }

    /**
     * Relasi ke barang inventory.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    /**
     * Relasi ke user pencatat.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
