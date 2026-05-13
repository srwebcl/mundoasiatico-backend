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
        'discount_amount',
        'coupon_code',
        'shipping_type',
        'shipping_address',
        'tracking_number',
        'shipping_carrier',
        'shipped_at',
        'admin_notes',
        'transbank_token',
        'transbank_authorization_code',
        'transbank_transaction_date',
        'banchile_request_id',
        'banchile_process_url',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'    => 'integer',
            'discount_amount' => 'integer',
            'shipping_address' => 'array',
            'shipped_at'       => 'datetime',
        ];
    }

    // =========================================================================
    // Constantes de Estado
    // =========================================================================

    const STATUS_PENDING   = 'pending';
    const STATUS_PAID      = 'paid';
    const STATUS_FAILED    = 'failed';
    const STATUS_PROCESSING = 'processing'; // preparando el pedido
    const STATUS_SHIPPED   = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING    => 'Pendiente',
        self::STATUS_PAID       => 'Pagado',
        self::STATUS_FAILED     => 'Fallido',
        self::STATUS_PROCESSING => 'En preparación',
        self::STATUS_SHIPPED    => 'Despachado',
        self::STATUS_DELIVERED  => 'Entregado',
        self::STATUS_CANCELLED  => 'Cancelado',
    ];

    const CARRIERS = [
        'starken'    => 'Starken',
        'chilexpress' => 'Chilexpress',
        'bluexpress' => 'BlueExpress',
        'correos'    => 'Correos de Chile',
    ];

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

    // =========================================================================
    // Helpers
    // =========================================================================

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [self::STATUS_PAID, self::STATUS_PROCESSING, self::STATUS_SHIPPED, self::STATUS_DELIVERED]);
    }
}
