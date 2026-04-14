<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso LIGERO para el listado paginado del catálogo.
 * No incluye descripción completa ni car_models para reducir payload.
 */
class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isWholesale = $request->user()?->isWholesale() ?? false;

        return [
            'id'              => $this->id,
            'sku'             => $this->sku,
            'name'            => $this->name,
            'slug'            => $this->slug,
            'image'           => $this->image ? asset('storage/' . $this->image) : null,
            'regular_price'   => $this->regular_price,
            'wholesale_price' => $this->wholesale_price,
            'active_price'    => $isWholesale ? $this->wholesale_price : $this->regular_price,
            'is_wholesale'    => $isWholesale,
            'stock'           => $this->stock,
            'in_stock'        => $this->stock > 0,
            'category'        => [
                'id'   => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ],
            'brand'           => [
                'id'   => $this->brand?->id,
                'name' => $this->brand?->name,
                'slug' => $this->brand?->slug,
            ],
        ];
    }
}
