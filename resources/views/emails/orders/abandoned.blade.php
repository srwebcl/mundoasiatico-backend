<x-mail::message>
# ¡Hola {{ $order->customer_name ?? 'amigo' }}! 👋

Vimos que dejaste algunos repuestos en tu carrito y no completaste tu compra. 
¡No te preocupes! Hemos guardado tus artículos por un tiempo limitado.

Para agradecer tu preferencia y animarte a terminar tu compra hoy mismo, te regalamos un **cupón de descuento**.

<x-mail::panel>
**Tu Cupón de Regalo:** `{{ $coupon->code }}`
<br>
*(Válido por 24 horas y otorga un {{ $coupon->discount_percentage }}% de descuento en tu total)*
</x-mail::panel>

### Esto es lo que dejaste en tu carrito:

<x-mail::table>
| Producto       | Cantidad         |
| :------------- |:-------------:|
@foreach($order->items as $item)
| {{ $item->product ? $item->product->name : 'Producto Eliminado' }} | {{ $item->quantity }} |
@endforeach
</x-mail::table>

<x-mail::button :url="config('app.frontend_url') . '/checkout'" color="red">
Volver a mi carrito
</x-mail::button>

Si tienes dudas sobre tus repuestos, responde este correo o escríbenos a nuestro WhatsApp.

Saludos,<br>
**Mundo Asiático**
</x-mail::message>
