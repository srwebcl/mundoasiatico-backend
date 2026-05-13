<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_amount',
        'max_uses', 'used_count', 'is_active', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'value'      => 'integer',
            'min_amount' => 'integer',
            'max_uses'   => 'integer',
            'used_count' => 'integer',
            'is_active'  => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeValid($query)
    {
        return $query
            ->where('is_active', true)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->where(fn($q) => $q->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Calcula el descuento en pesos para un monto dado.
     */
    public function calculateDiscount(int $amount): int
    {
        if ($this->type === 'percent') {
            return (int) round($amount * $this->value / 100);
        }
        return min($this->value, $amount); // descuento fijo, nunca mayor al total
    }
}
