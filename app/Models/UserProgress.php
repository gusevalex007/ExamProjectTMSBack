<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'telegram_id',
        'product_id',
        'progress',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'progress' => 'integer',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Пользователь
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Продукт (курс/урок)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Проверка завершённости
     */
    public function isCompleted(): bool
    {
        return $this->progress >= 100 && $this->completed_at !== null;
    }

    /**
     * Отметить как завершённое
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'progress' => 100,
            'completed_at' => now(),
        ]);
    }

    /**
     * Обновить прогресс
     */
    public function updateProgress(int $progress): void
    {
        $progress = max(0, min(100, $progress));

        $data = ['progress' => $progress];

        if ($progress >= 100 && !$this->completed_at) {
            $data['completed_at'] = now();
        }

        $this->update($data);
    }
}