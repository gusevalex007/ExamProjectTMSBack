<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'file_url',
        'file_name',
        'file_type',
        'file_size',
        'download_count',
        'is_active',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'download_count' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Связь с продуктом
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Инкрементировать счётчик скачиваний
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    /**
     * Получить полный URL файла
     */
    public function getFullUrlAttribute(): string
    {
        // Если уже полный URL
        if (filter_var($this->file_url, FILTER_VALIDATE_URL)) {
            return $this->file_url;
        }

        // Если относительный путь в storage
        return Storage::url($this->file_url);
    }

    /**
     * Форматированный размер файла
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) return 'N/A';

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}