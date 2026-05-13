@component('mail::message')
# 🚚 ¡Tu pedido está en camino!

Hola **{{ $order->customer_name }}**,

Te informamos que tu pedido **#{{ $order->id }}** fue despachado el {{ \Carbon\Carbon::parse($order->shipped_at)->format('d/m/Y') }}.

---

@if($order->tracking_number)
**Número de seguimiento:** `{{ $order->tracking_number }}`

**Transportista:** {{ ucfirst($order->shipping_carrier ?? 'Starken') }}

@component('mail::button', ['url' => 'https://www.starken.cl/seguimiento?codigo=' . $order->tracking_number, 'color' => 'red'])
Rastrear mi envío
@endcomponent

> 💡 Usa el número de seguimiento en el sitio web de {{ ucfirst($order->shipping_carrier ?? 'Starken') }} para ver el estado de tu envío en tiempo real.
@endif

---

**Dirección de entrega:**
@if($order->shipping_address)
{{ $order->shipping_address['street'] ?? '' }} #{{ $order->shipping_address['number'] ?? '' }}
{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['region'] ?? '' }}
@endif

---

¿Algún problema con tu envío? Contáctanos:
- **WhatsApp:** +569 7160 2029
- **Email:** ventas@mundoasiatico.cl

Gracias por tu preferencia,

**Mundo Asiático**
@endcomponent
