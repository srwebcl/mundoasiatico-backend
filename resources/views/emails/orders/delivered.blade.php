<x-mail::message>
# ¡Pedido Entregado!

Hola **{{ $order->customer_name }}**,

Nos complace informarte que tu pedido **#{{ $order->id }}** ha sido entregado exitosamente. 

Esperamos que disfrutes tus nuevos repuestos y que tu vehículo quede impecable. 

<x-mail::button :url="config('app.frontend_url')">
Visitar la tienda
</x-mail::button>

¿Cómo fue tu experiencia? Si tienes cualquier duda, recuerda que puedes contactarnos vía WhatsApp o respondiendo a este correo.

¡Gracias por confiar en **Mundo Asiático**!

Saludos,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
