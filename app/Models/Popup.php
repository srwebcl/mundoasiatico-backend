<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Popup extends Model
{
    protected $fillable = [
        'title',
        'image',
        'content',
        'button_text',
        'button_link',
        'delay_seconds',
        'target_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'delay_seconds' => 'integer',
        ];
    }
}
