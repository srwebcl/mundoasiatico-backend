<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_rut',
        'status',
        'total_amount',
        'shipping_type',
        'shipping_address',
        'transbank_token',
        'transbank_authorization_code',
        'transbank_transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'    => 'integer',
            'shipping_address' => 'array', // JSON auto-cast
        ];
    }

    // =========================================================================
    // Constantes de Estado
    // =========================================================================

    const STATUS_PENDING   = 'pending';
    const STATUS_PAID      = 'paid';
    const STATUS_FAILED    = 'failed';
    const STATUS_SHIPPED   = 'shipped';
    const STATUS_CANCELLED = 'cancelled';

    // =========================================================================
    // Relaciones
    // =========================================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
