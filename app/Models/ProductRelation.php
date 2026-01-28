<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'child_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Родительский продукт (курс)
     */
    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    /**
     * Дочерний продукт (урок)
     */
    public function child()
    {
        return $this->belongsTo(Product::class, 'child_id');
    }
}