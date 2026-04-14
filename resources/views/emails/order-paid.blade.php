<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pedido Confirmado — Mundo Asiático</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #18181b; }
        .wrapper { max-width: 620px; margin: 32px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }

        /* ── Header ── */
        .header { background: #09090b; padding: 32px 40px; text-align: center; }
        .header-logo { font-size: 22px; font-weight: 900; color: #ffffff; letter-spacing: -0.5px; text-transform: uppercase; }
        .header-logo span { color: #ef4444; }
        .header-tagline { font-size: 12px; color: #71717a; margin-top: 4px; letter-spacing: 0.1em; text-transform: uppercase; }

        /* ── Hero Banner ── */
        .hero { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 36px 40px; text-align: center; }
        .hero-icon { font-size: 48px; margin-bottom: 12px; }
        .hero-title { font-size: 26px; font-weight: 800; color: #ffffff; }
        .hero-subtitle { font-size: 14px; color: #fecaca; margin-top: 6px; }

        /* ── Secciones ── */
        .section { padding: 28px 40px; }
        .section-title { font-size: 11px; font-weight: 700; color: #71717a; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #f4f4f5; }

        /* ── Datos del pedido ── */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .info-item { background: #fafafa; border: 1px solid #f4f4f5; border-radius: 8px; padding: 12px 16px; }
        .info-label { font-size: 11px; font-weight: 600; color: #a1a1aa; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .info-value { font-size: 14px; font-weight: 600; color: #18181b; }
        .info-value.highlight { color: #ef4444; font-size: 18px; font-weight: 800; }

        /* ── Tabla de productos ── */
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table thead tr { background: #fafafa; }
        .items-table th { font-size: 11px; font-weight: 700; color: #71717a; text-transform: uppercase; letter-spacing: 0.08em; padding: 10px 12px; text-align: left; border-bottom: 1px solid #f4f4f5; }
        .items-table td { font-size: 13px; padding: 12px 12px; border-bottom: 1px solid #f9f9f9; vertical-align: top; }
        .item-name { font-weight: 600; color: #18181b; }
        .item-sku { font-size: 11px; color: #a1a1aa; font-family: monospace; margin-top: 2px; }
        .item-qty { text-align: center; color: #52525b; }
        .item-price { text-align: right; font-weight: 600; color: #18181b; }
        .total-row td { padding: 14px 12px; font-weight: 800; font-size: 15px; background: #fafafa; }
        .total-amount { color: #ef4444; font-size: 18px; }

        /* ── Shipping ── */
        .shipping-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: flex-start; gap: 12px; }
        .shipping-icon { font-size: 24px; flex-shrink: 0; }
        .shipping-label { font-size: 11px; font-weight: 700; color: #16a34a; text-transform: uppercase; letter-spacing: 0.08em; }
        .shipping-value { font-size: 14px; font-weight: 600; color: #15803d; margin-top: 2px; }
        .shipping-address { font-size: 12px; color: #16a34a; margin-top: 4px; }

        /* ── CTA ── */
        .cta-section { padding: 24px 40px; text-align: center; background: #fafafa; }
        .cta-text { font-size: 14px; color: #71717a; margin-bottom: 16px; }
        .cta-button { display: inline-block; background: #18181b; color: #ffffff; font-size: 13px; font-weight: 700; text-decoration: none; padding: 12px 28px; border-radius: 24px; letter-spacing: 0.04em; }
        .cta-button:hover { background: #ef4444; }

        /* ── Footer ── */
        .footer { padding: 24px 40px; text-align: center; border-top: 1px solid #f4f4f5; }
        .footer-text { font-size: 11px; color: #a1a1aa; line-height: 1.7; }
        .footer-link { color: #ef4444; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- Header -->
    <div class="header">
        <div class="header-logo">Mundo <span>Asiático</span></div>
        <div class="header-tagline">Repuestos para Vehículos Chinos</div>
    </div>

    <!-- Hero -->
    <div class="hero">
        <div class="hero-icon">✅</div>
        <div class="hero-title">¡Tu pedido fue confirmado!</div>
        <div class="hero-subtitle">Hola {{ $order->customer_name }}, tu pago fue procesado exitosamente.</div>
    </div>

    <!-- Datos del Pedido -->
    <div class="section">
        <div class="section-title">Resumen del Pedido</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Número de Orden</div>
                <div class="info-value">#{{ $order->id }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Fecha</div>
                <div class="info-value">{{ $order->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $order->customer_email }}</div>
            </div>
            @if($order->transbank_authorization_code)
            <div class="info-item">
                <div class="info-label">Código Autorización</div>
                <div class="info-value" style="font-family: monospace; font-size: 12px;">{{ $order->transbank_authorization_code }}</div>
            </div>
            @endif
            <div class="info-item" style="grid-column: 1 / -1;">
                <div class="info-label">Total Pagado</div>
                <div class="info-value highlight">${{ number_format($order->total_amount, 0, ',', '.') }} CLP</div>
            </div>
        </div>
    </div>

    <!-- Productos -->
    <div class="section" style="padding-top: 0;">
        <div class="section-title">Productos Comprados</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th style="text-align: center;">Cant.</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->product_name }}</div>
                        <div class="item-sku">SKU: {{ $item->product_sku }}</div>
                    </td>
                    <td class="item-qty">{{ $item->quantity }}x</td>
                    <td class="item-price">${{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2">Total</td>
                    <td class="item-price total-amount">${{ number_format($order->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Envío -->
    <div class="section" style="padding-top: 0;">
        <div class="section-title">Entrega</div>
        <div class="shipping-box">
            <div class="shipping-icon">
                @if($order->shipping_type === 'starken') 🚚 @else 🏢 @endif
            </div>
            <div>
                <div class="shipping-label">Método de Envío</div>
                <div class="shipping-value">{{ $shippingLabel }}</div>
                @if($order->shipping_type === 'starken' && $order->shipping_address)
                    <div class="shipping-address">
                        📍 {{ $order->shipping_address['street'] }} #{{ $order->shipping_address['number'] }}
                        @if(!empty($order->shipping_address['apto'])), {{ $order->shipping_address['apto'] }}@endif
                        — {{ $order->shipping_address['city'] }}, {{ $order->shipping_address['region'] }}
                    </div>
                @else
                    <div class="shipping-address">Te avisaremos cuando esté listo para retirar.</div>
                @endif
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="cta-section">
        <p class="cta-text">¿Tienes dudas sobre tu pedido? Estamos para ayudarte por WhatsApp o email.</p>
        <a href="https://wa.me/56912345678?text=Hola!+Mi+n%C3%BAmero+de+orden+es+%23{{ $order->id }}" class="cta-button">
            💬 Contactar por WhatsApp
        </a>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p class="footer-text">
            Este correo fue enviado a <strong>{{ $order->customer_email }}</strong> porque realizaste una compra en Mundo Asiático.<br />
            Si tienes dudas, escríbenos a <a href="mailto:pedidos@mundoasiatico.cl" class="footer-link">pedidos@mundoasiatico.cl</a><br /><br />
            © {{ date('Y') }} Mundo Asiático. Todos los derechos reservados.<br />
            <a href="{{ config('app.frontend_url') }}/terminos" class="footer-link">Términos y Condiciones</a> ·
            <a href="{{ config('app.frontend_url') }}/privacidad" class="footer-link">Privacidad</a>
        </p>
    </div>

</div>
</body>
</html>
