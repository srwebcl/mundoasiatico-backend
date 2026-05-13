<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingScript extends Model
{
    protected $fillable = [
        'name',
        'type',
        'code',
        'placement',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
