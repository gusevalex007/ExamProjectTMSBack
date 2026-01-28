<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
      // ДОБАВИТЬ к существующему $fillable:
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'type',
        'template_type',  // <-- НОВОЕ
        'category',       // <-- ДОБАВИТЬ
        'description',
        'status',
        'theme_config',
        'settings',
        'bot_token',
        'web_app_url',
    ];

    // Существующие casts + ДОБАВИТЬ:
    protected $casts = [
        'theme_config' => 'array',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Проверка типа шаблона
     */
    public function isShop(): bool
    {
        return $this->template_type === self::TEMPLATE_SHOP;
    }

    public function isCatalog(): bool
    {
        return $this->template_type === self::TEMPLATE_CATALOG;
    }

    public function isDigitalProducts(): bool
    {
        return $this->template_type === self::TEMPLATE_DIGITAL_PRODUCTS;
    }

    public function isLms(): bool
    {
        return $this->template_type === self::TEMPLATE_LMS;
    }

    /**
     * Получить разрешённые типы продуктов для этого шаблона
     */
  public function getAllowedProductTypes(): array
{

    logger()->info("Getting allowed product types for template type: " . $this->template_type);

    return match($this->template_type) {
        'shop', 
        'catalog' => [Product::TYPE_PRODUCT],

        'digital_products' => [Product::TYPE_DIGITAL_PRODUCT],

        'lms' => [Product::TYPE_COURSE, Product::TYPE_LESSON],

        'portfolio' => [Product::TYPE_PORTFOLIO_ITEM],

        'restaurant' => [Product::TYPE_MENU_ITEM],

        default => [Product::TYPE_PRODUCT],
    };
}

    /**
     * Получить дефолтный тип продукта для этого шаблона
     */
    public function getDefaultProductType(): string
    {
        return match($this->template_type) {
            'digital_products' => Product::TYPE_DIGITAL_PRODUCT,
            'lms' => Product::TYPE_COURSE,
            'portfolio' => Product::TYPE_PORTFOLIO_ITEM,
            'restaurant' => Product::TYPE_MENU_ITEM,
            'shop', 'catalog' => Product::TYPE_PRODUCT,
            default => Product::TYPE_PRODUCT,
        };
    }
}