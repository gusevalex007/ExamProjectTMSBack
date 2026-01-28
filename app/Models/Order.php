<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
    'project_id',
    'user_id',
    'telegram_id',
    'customer_id',     
    'customer_name',
    'customer_phone',
    'customer_email',
    'shipping_address',
    'total_amount',
    'status',
    'order_type',      
    'order_number',      
    'payment_method',
    'delivery_method',
    'notes',
];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // === НОВЫЕ КОНСТАНТЫ ===

const ORDER_TYPE_PURCHASE         = 'purchase';
const ORDER_TYPE_QUOTE_REQUEST    = 'quote_request';
const ORDER_TYPE_COURSE_ENROLLMENT = 'course_enrollment';
const ORDER_TYPE_DIGITAL_DOWNLOAD = 'digital_download';

// === НОВЫЕ МЕТОДЫ ===

public function isPurchase(): bool
{
    return $this->order_type === self::ORDER_TYPE_PURCHASE;
}

public function isQuoteRequest(): bool
{
    return $this->order_type === self::ORDER_TYPE_QUOTE_REQUEST;
}

public function isCourseEnrollment(): bool
{
    return $this->order_type === self::ORDER_TYPE_COURSE_ENROLLMENT;
}

public function isDigitalDownload(): bool
{
    return $this->order_type === self::ORDER_TYPE_DIGITAL_DOWNLOAD;
}
}
