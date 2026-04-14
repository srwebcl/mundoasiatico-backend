<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarModelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'brand'      => new BrandResource($this->whenLoaded('brand')),
            'year_start' => $this->year_start,
            'year_end'   => $this->year_end,
        ];
    }
}
