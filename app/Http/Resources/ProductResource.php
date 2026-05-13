<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso COMPLETO para la página de detalle del producto.
 */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isWholesale = $request->user()?->isWholesale() ?? false;

        return [
            'id'              => $this->id,
            'sku'             => $this->sku,
            'name'            => $this->name,
            'slug'            => $this->slug,
            'description'     => $this->description,
            'image'           => $this->image ? asset('storage/' . $this->image) : null,
            'gallery'         => collect($this->gallery ?? [])->map(fn($path) => asset('storage/' . $path))->toArray(),
            'regular_price'   => $this->regular_price,
            'wholesale_price' => $this->wholesale_price,
            'active_price'    => $isWholesale ? $this->wholesale_price : $this->regular_price,
            'is_wholesale'    => $isWholesale,
            'stock'           => $this->stock,
            'in_stock'        => $this->stock > 0,
            'is_featured'     => $this->is_featured,
            'category'        => new CategoryResource($this->whenLoaded('category')),
            'brand'           => new BrandResource($this->whenLoaded('brand')),
            'compatible_models' => CarModelResource::collection($this->whenLoaded('carModels')),
        ];
    }
}
