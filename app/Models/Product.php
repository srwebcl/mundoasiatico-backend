<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'description',
        'regular_price',
        'wholesale_price',
        'stock',
        'image',
        'is_active',
        'is_featured',
        'category_id',
        'brand_id',
    ];

    protected function casts(): array
    {
        return [
            'regular_price'   => 'integer',
            'wholesale_price' => 'integer',
            'stock'           => 'integer',
            'is_active'       => 'boolean',
            'is_featured'     => 'boolean',
        ];
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    // =========================================================================
    // Relaciones
    // =========================================================================

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Modelos de auto compatibles (pivote)
     */
    public function carModels()
    {
        return $this->belongsToMany(CarModel::class, 'car_model_product');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
