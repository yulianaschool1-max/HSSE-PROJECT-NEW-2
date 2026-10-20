<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class MediaAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'module',
        'disk',
        'file_path',
        'original_name',
        'file_name',
        'mime_type',
        'extension',
        'original_size',
        'compressed_size',
        'width',
        'height',
        'uploaded_by',
        'caption',
    ];

    protected $casts = [
        'original_size' => 'integer',
        'compressed_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    protected $appends = [
        'file_url',
        'compression_ratio',
    ];

    /**
     * Polymorphic relation to parent model.
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get accessible public URL for the media file.
     */
    public function getFileUrlAttribute(): string
    {
        if (empty($this->file_path)) {
            return '';
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        if (str_starts_with($this->file_path, 'uploads/')) {
            return asset($this->file_path);
        }

        return Storage::disk($this->disk ?? 'public')->url($this->file_path);
    }

    /**
     * Compression saving percentage.
     */
    public function getCompressionRatioAttribute(): float
    {
        if ($this->original_size > 0 && $this->compressed_size > 0 && $this->compressed_size < $this->original_size) {
            return round((($this->original_size - $this->compressed_size) / $this->original_size) * 100, 1);
        }

        return 0.0;
    }
}

