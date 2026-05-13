@component('mail::message')
# ¡Bienvenido a Mundo Asiático! 🎉

Hola **{{ $user->name }}**,

Tu cuenta ha sido creada exitosamente. Ahora puedes:

- 🛒 **Comprar** repuestos para tu vehículo chino con despacho a todo Chile
- 📦 **Rastrear** el estado de tus pedidos
- 💼 **Solicitar precios mayoristas** si eres taller o lubricentro

@component('mail::button', ['url' => config('app.frontend_url', 'https://mundoasiatico.cl') . '/catalogo', 'color' => 'red'])
Ver Catálogo
@endcomponent

---

Si tienes alguna duda, escríbenos por WhatsApp al **+569 7160 2029** o a **ventas@mundoasiatico.cl**.

¡Muchas gracias por registrarte!

**El equipo de Mundo Asiático**
@endcomponent
