@component('mail::message')
# ✅ ¡Tu pedido fue confirmado!

Hola **{{ $order->customer_name }}**,

Tu pago fue procesado correctamente. A continuación el resumen de tu compra:

---

**Orden #{{ $order->id }}**

@component('mail::table')
| Producto | Cant. | Precio Unit. | Total |
|---|---|---|---|
@foreach($order->items as $item)
| {{ $item->product->name ?? $item->product_name }} | {{ $item->quantity }} | ${{ number_format($item->unit_price, 0, ',', '.') }} | ${{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }} |
@endforeach
@endcomponent

@if($order->discount_amount > 0)
💰 **Descuento aplicado:** -${{ number_format($order->discount_amount, 0, ',', '.') }}
@endif

**Total pagado: ${{ number_format($order->total_amount, 0, ',', '.') }}**

---

**Método de entrega:**
@if($order->shipping_type === 'retiro_stgo')
🏢 Retiro en Casa Matriz — Av. Concha y Toro 3063 Local 24, Puente Alto, Santiago.
Disponible en 24–48 hrs hábiles.
@elseif($order->shipping_type === 'retiro_pm')
🏪 Retiro en Sucursal Puerto Montt — Urmeneta 882 Local 1.
Listo en máx. 3 días hábiles.
@else
🚚 Envío a domicilio por Starken. Te avisaremos cuando tu pedido sea despachado.
@endif

@if($order->shipping_type === 'starken' && $order->shipping_address)
**Dirección de envío:**
{{ $order->shipping_address['street'] ?? '' }} #{{ $order->shipping_address['number'] ?? '' }}
{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['region'] ?? '' }}
@endif

---

¿Tienes alguna duda? Escríbenos por WhatsApp al **+569 7160 2029** o responde este correo.

@component('mail::button', ['url' => config('app.frontend_url', 'https://mundoasiatico.cl') . '/mi-cuenta/ordenes', 'color' => 'red'])
Ver mis pedidos
@endcomponent

¡Gracias por comprar en Mundo Asiático!

{{ config('app.name') }}
@endcomponent
