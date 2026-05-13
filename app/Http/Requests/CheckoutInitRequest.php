<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutInitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Disponible también para invitados (sin Sanctum)
    }

    public function rules(): array
    {
        return [
            // Datos del cliente
            'customer_name'         => ['required', 'string', 'max:255'],
            'customer_email'        => ['required', 'email', 'max:255'],
            'customer_phone'        => ['nullable', 'string', 'max:20'],
            'customer_rut'          => ['nullable', 'string', 'max:12'],

            // Logística
            'shipping_type'         => ['required', Rule::in(['retiro_stgo', 'retiro_pm', 'starken'])],

            // Dirección (solo requerida si es Starken)
            'shipping_address'                 => ['required_if:shipping_type,starken', 'nullable', 'array'],
            'shipping_address.region'          => ['required_if:shipping_type,starken', 'string', 'max:100'],
            'shipping_address.city'            => ['required_if:shipping_type,starken', 'string', 'max:100'],
            'shipping_address.street'          => ['required_if:shipping_type,starken', 'string', 'max:255'],
            'shipping_address.number'          => ['required_if:shipping_type,starken', 'string', 'max:20'],
            'shipping_address.apto'            => ['nullable', 'string', 'max:50'],

            // Ítems del carrito
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1', 'max:99'],
            
            // Cupón
            'coupon_code'           => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required'            => 'El nombre del cliente es obligatorio.',
            'customer_email.required'           => 'El correo del cliente es obligatorio.',
            'shipping_type.required'            => 'Debes seleccionar un método de envío.',
            'shipping_type.in'                  => 'El método de envío no es válido.',
            'shipping_address.region.required_if' => 'La región es obligatoria para envío a domicilio.',
            'shipping_address.city.required_if'   => 'La comuna es obligatoria para envío a domicilio.',
            'shipping_address.street.required_if' => 'La calle es obligatoria para envío a domicilio.',
            'shipping_address.number.required_if' => 'El número es obligatorio para envío a domicilio.',
            'items.required'                    => 'El carrito no puede estar vacío.',
            'items.*.product_id.exists'         => 'Uno o más productos no existen en nuestro catálogo.',
            'items.*.quantity.min'              => 'La cantidad mínima es 1.',
        ];
    }
}
