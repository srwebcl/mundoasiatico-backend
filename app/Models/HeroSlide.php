<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path',
        'subtitle',
        'title',
        'description',
        'cta_text',
        'cta_link',
        'is_active',
        'order',
    ];
}
