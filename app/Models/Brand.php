<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'logo', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // =========================================================================
    // Relaciones
    // =========================================================================

    /**
     * Modelos de auto que pertenecen a esta marca (ej: Chery → Tiggo 2, Tiggo 4...)
     */
    public function carModels()
    {
        return $this->hasMany(CarModel::class);
    }

    /**
     * Repuestos que tienen esta marca asignada (marca del repuesto en sí, no del auto)
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
