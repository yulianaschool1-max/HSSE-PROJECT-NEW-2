<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_items';

    public const KATEGORI_LIST = [
        'APD (Alat Pelindung Diri)',
        'Peralatan Emergency & Rescue',
        'Detektor & Instrumen Lingkungan',
        'Alat Pemadam Api (Fire Protection)',
        'Perlengkapan Kerja di Ketinggian',
        'Rambu & Barikade Keselamatan',
        'P3K & Medis Lapangan',
    ];

    protected $fillable = [
        'name',
        'item_code',
        'kode_barang',
        'item_name',
        'nama_barang',
        'category',
        'kategori',
        'unit_measure',
        'satuan',
        'stock',
        'stok_sekarang',
        'minimum_stock',
        'stok_minimum',
        'location',
        'lokasi_default',
        'condition',
        'description',
        'keterangan',
        'is_active',
    ];

    public function setNameAttribute($value): void
    {
        $this->attributes['item_name'] = $value;
        $this->attributes['nama_barang'] = $value;
    }

    public function getIsConsumableAttribute(): bool
    {
        $cat = strtolower((string) ($this->category ?? $this->kategori ?? ''));
        return str_contains($cat, 'habis pakai') || str_contains($cat, 'consumable');
    }

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'stok_sekarang' => 'integer',
            'minimum_stock' => 'integer',
            'stok_minimum' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InventoryItem $item) {
            // Sinkronisasi item_code <-> kode_barang
            if (empty($item->kode_barang) && !empty($item->item_code)) {
                $item->kode_barang = $item->item_code;
            } elseif (empty($item->item_code) && !empty($item->kode_barang)) {
                $item->item_code = $item->kode_barang;
            }

            // Sinkronisasi item_name <-> nama_barang
            if (empty($item->nama_barang) && !empty($item->item_name)) {
                $item->nama_barang = $item->item_name;
            } elseif (empty($item->item_name) && !empty($item->nama_barang)) {
                $item->item_name = $item->nama_barang;
            }

            // Sinkronisasi category <-> kategori
            if (empty($item->kategori) && !empty($item->category)) {
                $item->kategori = $item->category;
            } elseif (empty($item->category) && !empty($item->kategori)) {
                $item->category = $item->kategori;
            }

            // Sinkronisasi unit_measure <-> satuan
            if (empty($item->satuan) && !empty($item->unit_measure)) {
                $item->satuan = $item->unit_measure;
            } elseif (empty($item->unit_measure) && !empty($item->satuan)) {
                $item->unit_measure = $item->satuan;
            }

            // Sinkronisasi stock <-> stok_sekarang
            if (($item->stok_sekarang === null || $item->stok_sekarang === 0) && $item->stock) {
                $item->stok_sekarang = $item->stock;
            } elseif (($item->stock === null || $item->stock === 0) && $item->stok_sekarang) {
                $item->stock = $item->stok_sekarang;
            }

            // Sinkronisasi minimum_stock <-> stok_minimum
            if (($item->stok_minimum === null || $item->stok_minimum === 0) && $item->minimum_stock) {
                $item->stok_minimum = $item->minimum_stock;
            } elseif (($item->minimum_stock === null || $item->minimum_stock === 0) && $item->stok_minimum) {
                $item->minimum_stock = $item->stok_minimum;
            }

            // Sinkronisasi location <-> lokasi_default
            if (empty($item->lokasi_default) && !empty($item->location)) {
                $item->lokasi_default = $item->location;
            } elseif (empty($item->location) && !empty($item->lokasi_default)) {
                $item->location = $item->lokasi_default;
            }

            // Sinkronisasi description <-> keterangan
            if (empty($item->keterangan) && !empty($item->description)) {
                $item->keterangan = $item->description;
            } elseif (empty($item->description) && !empty($item->keterangan)) {
                $item->description = $item->keterangan;
            }
        });
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(InventoryBorrowing::class, 'inventory_item_id');
    }

    public function activeBorrowings(): HasMany
    {
        return $this->hasMany(InventoryBorrowing::class, 'inventory_item_id')
            ->where('status', '!=', InventoryBorrowing::STATUS_RETURNED)
            ->whereRaw('quantity_borrowed > quantity_returned');
    }

    public function getCurrentStockAttribute(): int
    {
        return (int) ($this->stock ?? $this->stok_sekarang ?? 0);
    }

    public function getBorrowedStockAttribute(): int
    {
        return (int) $this->borrowings()
            ->where('status', '!=', InventoryBorrowing::STATUS_RETURNED)
            ->sum(\Illuminate\Support\Facades\DB::raw('quantity_borrowed - quantity_returned'));
    }

    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->current_stock - $this->borrowed_stock);
    }

    public function getBarangIdAttribute(): ?string
    {
        return $this->item_code ?? $this->kode_barang;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->current_stock <= (int) ($this->minimum_stock ?? $this->stok_minimum ?? 0);
    }
}
