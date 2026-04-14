<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                           => $this->id,
            'status'                       => $this->status,
            'total_amount'                 => $this->total_amount,
            'customer_name'                => $this->customer_name,
            'customer_email'               => $this->customer_email,
            'customer_phone'               => $this->customer_phone,
            'customer_rut'                 => $this->customer_rut,
            'shipping_type'                => $this->shipping_type,
            'shipping_address'             => $this->shipping_address,
            'transbank_authorization_code' => $this->transbank_authorization_code,
            'transbank_transaction_date'   => $this->transbank_transaction_date,
            'items'                        => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at'                   => $this->created_at?->toDateTimeString(),
        ];
    }
}
