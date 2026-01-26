<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'project_id',
        'telegram_id',
        'name',
        'username',
        'phone',
        'email',
        'orders_count',
        'total_spent'
    ];

    protected $casts = [
        'telegram_id' => 'integer',
        'orders_count' => 'integer',
        'total_spent' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
