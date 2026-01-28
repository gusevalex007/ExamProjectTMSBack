<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    // === НОВЫЕ КОНСТАНТЫ (добавить в начало класса) ===

    const TYPE_PRODUCT         = 'product';
    const TYPE_DIGITAL_PRODUCT = 'digital_product';
    const TYPE_COURSE          = 'course';
    const TYPE_LESSON          = 'lesson';
    const TYPE_PORTFOLIO_ITEM  = 'portfolio_item';
    const TYPE_MENU_ITEM       = 'menu_item';

    // ДОБАВИТЬ к существующему $fillable:
    protected $fillable = [
        'project_id',
        'category',
        'type',           // <-- НОВОЕ
        'name',
        'description',
        'price',
        'old_price',
        'stock',
        'discount',
        'image_url',
        'metadata',       // <-- если ещё нет
        'is_active',
        'is_featured',
        'rating',
        'reviews_count',
    ];

    // Существующие casts + ДОБАВИТЬ metadata:
    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'discount' => 'integer',
        'rating' => 'decimal:1',
        'reviews_count' => 'integer',
    ];


    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // === НОВЫЕ ОТНОШЕНИЯ (добавить в конец класса) ===

    /**
     * Файлы продукта (для digital products, LMS)
     */
    public function files()
    {
        return $this->hasMany(ProductFile::class);
    }

    /**
     * Дочерние продукты (для LMS: курс → уроки)
     */
    public function children()
    {
        return $this->hasMany(ProductRelation::class, 'parent_id')
            ->with('child')
            ->orderBy('sort_order');
    }

    /**
     * Родительский продукт (для LMS: урок → курс)
     */
    public function parent()
    {
        return $this->hasOne(ProductRelation::class, 'child_id')
            ->with('parent');
    }

    /**
     * Прогресс пользователей
     */
    public function progress()
    {
        return $this->hasMany(UserProgress::class);
    }

    // === НОВЫЕ SCOPES ===

    /**
     * Фильтр по типу
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Только активные
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Только избранные
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // === МЕТОДЫ ПРОВЕРКИ ТИПА ===

    public function isProduct(): bool
    {
        return $this->type === self::TYPE_PRODUCT;
    }

    public function isDigitalProduct(): bool
    {
        return $this->type === self::TYPE_DIGITAL_PRODUCT;
    }

    public function isCourse(): bool
    {
        return $this->type === self::TYPE_COURSE;
    }

    public function isLesson(): bool
    {
        return $this->type === self::TYPE_LESSON;
    }

    public function isPortfolioItem(): bool
    {
        return $this->type === self::TYPE_PORTFOLIO_ITEM;
    }

    public function isMenuItem(): bool
    {
        return $this->type === self::TYPE_MENU_ITEM;
    }

}