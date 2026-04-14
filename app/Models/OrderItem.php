<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'integer',
            'unit_price' => 'integer',
        ];
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Subtotal calculado del ítem.
     */
    public function getSubtotalAttribute(): int
    {
        return $this->quantity * $this->unit_price;
    }

    // =========================================================================
    // Relaciones
    // =========================================================================

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
