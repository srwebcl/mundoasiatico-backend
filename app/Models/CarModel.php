<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CarModel extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function ($carModel) {
            if (empty($carModel->slug)) {
                $baseSlug = Str::slug($carModel->name);
                $carModel->slug = $baseSlug . '-' . Str::random(5);
            }
        });
    }

    protected $fillable = ['name', 'slug', 'brand_id', 'year_start', 'year_end', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'year_start' => 'integer',
            'year_end'   => 'integer',
        ];
    }

    // =========================================================================
    // Relaciones
    // =========================================================================

    /**
     * Marca del vehículo (Chery, MG, JAC, Great Wall...)
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Repuestos compatibles con este modelo de auto (pivote)
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'car_model_product');
    }
}
