<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutInitRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Coupon;
use App\Mail\OrderConfirmedMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Dnetix\Redirection\PlacetoPay;

class CheckoutController extends Controller
{
    /**
     * POST /api/checkout/init
     *
     * 1. Valida los ítems y el stock.
     * 2. Crea la Order en estado "pending".
     * 3. Inicia la transacción con Transbank Webpay Plus.
     * 4. Devuelve la URL de redirección y el token al frontend.
     */
    public function init(CheckoutInitRequest $request): JsonResponse
    {
        // ── 1. Cargar productos desde la DB y verificar stock ─────────────────
        $itemIds  = collect($request->items)->pluck('product_id')->unique();
        $products = Product::whereIn('id', $itemIds)->active()->get()->keyBy('id');

        $cartItems   = [];
        $totalAmount = 0;

        foreach ($request->items as $item) {
            $product = $products->get($item['product_id']);

            if (! $product) {
                return response()->json([
                    'message' => "El producto #{$item['product_id']} no existe o está inactivo.",
                ], 422);
            }

            if ($product->stock < $item['quantity']) {
                return response()->json([
                    'message' => "Stock insuficiente para \"{$product->name}\". Disponible: {$product->stock}.",
                ], 422);
            }

            // Precio según rol del usuario (mayorista o retail)
            $isWholesale = $request->user()?->isWholesale() ?? false;
            $unitPrice   = $isWholesale ? $product->wholesale_price : $product->regular_price;

            $cartItems[] = [
                'product'    => $product,
                'quantity'   => $item['quantity'],
                'unit_price' => $unitPrice,
            ];

            $totalAmount += $unitPrice * $item['quantity'];
        }

        // ── 1.5. Calcular Descuentos (Cupones) ───────────────────────────────
        $discountAmount = 0;
        $coupon = null;

        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', $request->coupon_code)->where('is_active', true)->first();
            
            if (!$coupon) {
                return response()->json(['message' => 'El cupón ingresado no es válido o está inactivo.'], 422);
            }
            if ($coupon->expires_at && $coupon->expires_at->isPast()) {
                return response()->json(['message' => 'El cupón ingresado ha expirado.'], 422);
            }
            if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
                return response()->json(['message' => 'El cupón ha alcanzado su límite de usos.'], 422);
            }
            if ($totalAmount < $coupon->min_amount) {
                return response()->json(['message' => "El cupón requiere una compra mínima de $ {$coupon->min_amount}."], 422);
            }

            // Aplicar matemática del descuento
            if ($coupon->type === 'percent') {
                $discountAmount = (int) round($totalAmount * ($coupon->value / 100));
            } else {
                $discountAmount = $coupon->value;
            }

            // Asegurarse de no descontar más del total
            $discountAmount = min($discountAmount, $totalAmount);
        }

        $finalAmount = max(0, $totalAmount - $discountAmount);

        // ── 2. Crear la Order y reservar stock en transacción DB ─────────────
        try {
            $order = DB::transaction(function () use ($request, $cartItems, $totalAmount, $finalAmount, $discountAmount, $coupon) {
                $order = Order::create([
                    'user_id'          => $request->user()?->id,
                    'customer_name'    => $request->customer_name,
                    'customer_email'   => $request->customer_email,
                    'customer_phone'   => $request->customer_phone,
                    'customer_rut'     => $request->customer_rut,
                    'status'           => Order::STATUS_PENDING,
                    'total_amount'     => $finalAmount, // <- El total ya va con descuento
                    'discount_amount'  => $discountAmount,
                    'coupon_code'      => $coupon ? $coupon->code : null,
                    'shipping_type'    => $request->shipping_type,
                    'shipping_address' => $request->shipping_address,
                ]);

                foreach ($cartItems as $item) {
                    $product = $item['product'];

                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'product_sku'  => $product->sku,
                        'quantity'     => $item['quantity'],
                        'unit_price'   => $item['unit_price'],
                    ]);

                    // Reservar el stock
                    $product->increment('stock_reserved', $item['quantity']);
                }

                return $order;
            });
        } catch (\Exception $e) {
            Log::error('CheckoutController@init DB error: ' . $e->getMessage());
            return response()->json(['message' => 'Error al crear el pedido. Intenta nuevamente.'], 500);
        }

        // ── 3. Iniciar transacción Banchile Pagos (PlacetoPay) ──────────────────
        try {
            $placetopay = $this->getPlacetoPay();
            $reference  = 'ORD-' . $order->id . '-' . now()->timestamp;

            $requestData = [
                'payment' => [
                    'reference' => $reference,
                    'description' => 'Compra en Mundo Asiático - Orden #' . $order->id,
                    'amount' => [
                        'currency' => 'CLP',
                        'total' => $order->total_amount,
                    ],
                ],
                'expiration' => now()->addHour()->format('c'),
                // Agregamos ?token_ws= al final para no romper la lógica actual del frontend de Next.js
                'returnUrl' => config('app.frontend_url') . '/checkout/return?token_ws=' . $reference,
                'ipAddress' => request()->ip() ?? '127.0.0.1',
                'userAgent' => request()->userAgent() ?? 'MundoAsiatico/1.0',
            ];

            $response = $placetopay->request($requestData);

            if ($response->isSuccessful()) {
                // Guardar la referencia (como "token_ws") y la URL de redirección
                $order->update([
                    'banchile_request_id'  => $response->requestId(),
                    'banchile_process_url' => $response->processUrl(),
                    'transbank_token'      => $reference, // Usamos la misma columna para compatibilidad con el frontend
                ]);

                return response()->json([
                    'order_id'       => $order->id,
                    'webpay_url'     => $response->processUrl(),
                    'webpay_token'   => $reference, // Simula el token_ws para el front
                ]);
            } else {
                Log::error('Banchile Pagos Request Error: ' . $response->status()->message());
                $order->update(['status' => Order::STATUS_FAILED]);
                return response()->json(['message' => 'Error al conectar con Banchile Pagos: ' . $response->status()->message()], 502);
            }

        } catch (\Exception $e) {
            Log::error('CheckoutController@init Banchile error: ' . $e->getMessage());

            // Cancelar la orden creada
            $order->update(['status' => Order::STATUS_FAILED]);

            return response()->json([
                'message' => 'Error al iniciar el pago. Por favor contacta a soporte.',
            ], 502);
        }
    }

    /**
     * POST /api/checkout/return
     *
     * Webhook de retorno de Banchile Pagos.
     */
    public function return(Request $request): JsonResponse
    {
        // El frontend pasará ?token_ws= como lo hacía antes, que ahora tiene la 'reference'
        $reference = $request->input('token_ws') ?? $request->input('TBK_TOKEN');

        if (! $reference) {
            return response()->json(['message' => 'Referencia de pago no recibida.'], 400);
        }

        // Buscar la orden por la referencia guardada en transbank_token
        $order = Order::where('transbank_token', $reference)->first();

        if (! $order) {
            return response()->json(['message' => 'Orden no encontrada.'], 404);
        }

        // Si ya fue procesada, devolver éxito inmediato para no duplicar correos
        if ($order->status === Order::STATUS_PAID) {
            return response()->json([
                'status'           => 'paid',
                'order_id'         => $order->id,
                'authorization_code' => $order->transbank_authorization_code,
                'message'          => '¡Pago confirmado! Tu pedido está en proceso.',
            ]);
        }

        // ── Consultar estado de transacción a Banchile Pagos ──────────────────────
        try {
            $placetopay = $this->getPlacetoPay();
            $response = $placetopay->query($order->banchile_request_id);

            if ($response->isSuccessful()) {
                // Pago Aprobado
                DB::transaction(function () use ($order, $response) {
                    $order->update([
                        'status'                       => Order::STATUS_PAID,
                        'transbank_authorization_code' => $response->payment()[0]->authorization() ?? 'BCH-'.$order->banchile_request_id,
                        'transbank_transaction_date'   => $response->status()->date(),
                    ]);

                    foreach ($order->items()->with('product')->get() as $item) {
                        $product = $item->product;
                        if ($product) {
                            $product->decrement('stock', $item->quantity);
                            $product->decrement('stock_reserved', $item->quantity);
                        }
                    }

                    if ($order->coupon_code) {
                        \App\Models\Coupon::where('code', $order->coupon_code)->increment('used_count');
                    }
                });

                // Enviar correo
                try {
                    Mail::to($order->customer_email)->send(new OrderConfirmedMail($order));
                } catch (\Exception $e) {
                    Log::error("Error enviando OrderConfirmedMail (#{$order->id}): " . $e->getMessage());
                }

                return response()->json([
                    'status'           => 'paid',
                    'order_id'         => $order->id,
                    'authorization_code' => $order->transbank_authorization_code,
                    'message'          => '¡Pago confirmado por Banchile Pagos!',
                ]);

            } elseif ($response->isRejected()) {
                // Pago Rechazado o Fallido
                $order->update(['status' => Order::STATUS_FAILED]);
                
                // Liberar stock
                foreach ($order->items as $item) {
                    $item->product?->decrement('stock_reserved', $item->quantity);
                }

                return response()->json([
                    'status'  => 'failed',
                    'message' => 'El pago fue rechazado. Razón: ' . $response->status()->message(),
                ]);

            } else {
                // Pendiente u otro estado (cancelado)
                if ($response->status()->status() == 'PENDING') {
                    return response()->json([
                        'status'  => 'pending',
                        'message' => 'El pago aún está pendiente. Se validará más tarde.',
                    ]);
                } else {
                    $order->update(['status' => Order::STATUS_CANCELLED]);
                    
                    // Liberar stock
                    foreach ($order->items as $item) {
                        $item->product?->decrement('stock_reserved', $item->quantity);
                    }

                    return response()->json([
                        'status'  => 'cancelled',
                        'message' => 'El pago fue cancelado o expiró.',
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('CheckoutController@return Banchile error: ' . $e->getMessage(), [
                'requestId' => $order->banchile_request_id,
                'order_id' => $order->id,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al validar el pago. Contacta soporte con tu orden #' . $order->id,
            ], 502);
        }
    }

    /**
     * GET /api/orders/{id}
     * Consulta el estado de una orden (para la página /exito del frontend).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::with('items')->findOrFail($id);

        // Solo el dueño o un admin puede ver la orden
        if ($request->user() && $request->user()->id !== $order->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers Privados
    // ──────────────────────────────────────────────────────────────────────────

    private function getPlacetoPay(): PlacetoPay
    {
        return new PlacetoPay([
            'login'   => env('BANCHILE_LOGIN', 'ffb78b93826239e1aa85a515aa961bd9'),
            'tranKey' => env('BANCHILE_SECRET_KEY', 'U87nG0kcCsjb61Mj'),
            'baseUrl' => env('BANCHILE_URL', 'https://checkout.test.banchilepagos.cl'),
            'timeout' => 45,
        ]);
    }
}
