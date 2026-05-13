<x-mail::message>
# ¡Gracias por tu compra, {{ $order->customer_name }}!

Hemos recibido tu pedido **#{{ $order->id }}** y estamos procesándolo.
Te avisaremos apenas tu pedido cambie de estado.

<x-mail::panel>
**Resumen del Pedido:**
- **Fecha:** {{ $order->created_at->format('d/m/Y H:i') }}
- **Método de Envío:** {{ strtoupper($order->shipping_type) }}
@if($order->shipping_type === 'starken' && is_array($order->shipping_address))
- **Dirección:** {{ $order->shipping_address['street'] ?? '' }} {{ $order->shipping_address['number'] ?? '' }}, {{ $order->shipping_address['city'] ?? '' }}
@endif
- **Total Pagado:** ${{ number_format($order->total_price, 0, ',', '.') }}
</x-mail::panel>

<x-mail::table>
| Producto       | Cantidad         | Precio  |
| :------------- |:-------------:| --------:|
@foreach($order->items as $item)
| {{ $item->product ? $item->product->name : 'Producto Eliminado' }} | {{ $item->quantity }} | ${{ number_format($item->unit_price, 0, ',', '.') }} |
@endforeach
</x-mail::table>

<x-mail::button :url="config('app.frontend_url') . '/exito?order_id=' . $order->id">
Ver estado de mi pedido
</x-mail::button>

Gracias por preferir Mundo Asiático,<br>
El equipo de ventas.
</x-mail::message>
