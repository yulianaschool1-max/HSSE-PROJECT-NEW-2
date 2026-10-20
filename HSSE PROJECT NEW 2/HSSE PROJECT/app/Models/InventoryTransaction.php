<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $table = 'inventory_transactions';

    protected $fillable = [
        'inventory_item_id',
        'transaction_type',
        'jenis_transaksi',
        'quantity',
        'jumlah',
        'transaction_date',
        'tanggal',
        'unit',
        'person_in_charge',
        'petugas',
        'reference_number',
        'notes',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'jumlah' => 'integer',
            'transaction_date' => 'datetime',
            'tanggal' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InventoryTransaction $tx) {
            // Sinkronisasi transaction_type <-> jenis_transaksi
            if (empty($tx->jenis_transaksi) && !empty($tx->transaction_type)) {
                $tx->jenis_transaksi = $tx->transaction_type;
            } elseif (empty($tx->transaction_type) && !empty($tx->jenis_transaksi)) {
                $tx->transaction_type = $tx->jenis_transaksi;
            }

            // Sinkronisasi quantity <-> jumlah
            if (($tx->jumlah === null || $tx->jumlah === 0) && $tx->quantity) {
                $tx->jumlah = $tx->quantity;
            } elseif (($tx->quantity === null || $tx->quantity === 0) && $tx->jumlah) {
                $tx->quantity = $tx->jumlah;
            }

            // Sinkronisasi transaction_date <-> tanggal
            if (empty($tx->tanggal) && !empty($tx->transaction_date)) {
                $tx->tanggal = $tx->transaction_date;
            } elseif (empty($tx->transaction_date) && !empty($tx->tanggal)) {
                $tx->transaction_date = $tx->tanggal;
            }

            // Sinkronisasi person_in_charge <-> petugas
            if (empty($tx->petugas) && !empty($tx->person_in_charge)) {
                $tx->petugas = $tx->person_in_charge;
            } elseif (empty($tx->person_in_charge) && !empty($tx->petugas)) {
                $tx->person_in_charge = $tx->petugas;
            }

            // Sinkronisasi notes <-> keterangan
            if (empty($tx->keterangan) && !empty($tx->notes)) {
                $tx->keterangan = $tx->notes;
            } elseif (empty($tx->notes) && !empty($tx->keterangan)) {
                $tx->notes = $tx->keterangan;
            }
        });
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function getTanggalTransaksiAttribute()
    {
        return $this->transaction_date ?? $this->tanggal ?? $this->created_at;
    }

    public function getNamaPengisiAttribute(): string
    {
        return $this->person_in_charge ?? $this->petugas ?? 'Petugas HSSE';
    }

    public function getLokasiBarangAttribute(): ?string
    {
        return $this->item?->lokasi_default ?? $this->item?->location ?? 'Gudang HSSE';
    }

    public function getNomorDokumenAttribute(): ?string
    {
        return $this->reference_number;
    }
}

