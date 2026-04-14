<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutInitRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Transbank\Webpay\WebpayPlus\Transaction;
use Transbank\Webpay\Options;

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

        // ── 2. Crear la Order y sus items en una transacción DB ───────────────
        try {
            $order = DB::transaction(function () use ($request, $cartItems, $totalAmount) {
                $order = Order::create([
                    'user_id'          => $request->user()?->id,
                    'customer_name'    => $request->customer_name,
                    'customer_email'   => $request->customer_email,
                    'customer_phone'   => $request->customer_phone,
                    'customer_rut'     => $request->customer_rut,
                    'status'           => Order::STATUS_PENDING,
                    'total_amount'     => $totalAmount,
                    'shipping_type'    => $request->shipping_type,
                    'shipping_address' => $request->shipping_address,
                ]);

                foreach ($cartItems as $item) {
                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'product_sku'  => $item['product']->sku,
                        'quantity'     => $item['quantity'],
                        'unit_price'   => $item['unit_price'],
                    ]);
                }

                return $order;
            });
        } catch (\Exception $e) {
            Log::error('CheckoutController@init DB error: ' . $e->getMessage());
            return response()->json(['message' => 'Error al crear el pedido. Intenta nuevamente.'], 500);
        }

        // ── 3. Iniciar transacción Transbank Webpay Plus ──────────────────────
        try {
            $tx = $this->buildTransaction();

            $returnUrl = config('app.frontend_url') . '/checkout/return';
            $buyOrder  = 'ORD-' . $order->id . '-' . now()->timestamp;
            $sessionId = 'sess-' . $order->id;

            $response = $tx->create($buyOrder, $sessionId, $order->total_amount, $returnUrl);

            // Guardar el token de Transbank en la orden
            $order->update(['transbank_token' => $response->getToken()]);

            return response()->json([
                'order_id'       => $order->id,
                'webpay_url'     => $response->getUrl(),
                'webpay_token'   => $response->getToken(),
            ]);

        } catch (\Exception $e) {
            Log::error('CheckoutController@init Transbank error: ' . $e->getMessage());

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
     * Webhook de Transbank. Se llama cuando el usuario regresa del portal de pago.
     * Confirma la transacción, actualiza el estado de la orden y descuenta el stock.
     */
    public function return(Request $request): JsonResponse
    {
        $token = $request->input('token_ws') ?? $request->input('TBK_TOKEN');

        if (! $token) {
            return response()->json(['message' => 'Token de pago no recibido.'], 400);
        }

        // Buscar la orden por token
        $order = Order::where('transbank_token', $token)->first();

        if (! $order) {
            return response()->json(['message' => 'Orden no encontrada para este token.'], 404);
        }

        // Si el usuario canceló en Transbank (TBK_TOKEN sin token_ws)
        if ($request->has('TBK_TOKEN') && ! $request->has('token_ws')) {
            $order->update(['status' => Order::STATUS_CANCELLED]);
            return response()->json([
                'status'  => 'cancelled',
                'message' => 'El pago fue cancelado por el usuario.',
            ]);
        }

        // ── Confirmar transacción con Transbank ───────────────────────────────
        try {
            $tx       = $this->buildTransaction();
            $response = $tx->commit($token);

            // Validación del response code (0 = aprobado)
            if ($response->getResponseCode() !== 0) {
                $order->update(['status' => Order::STATUS_FAILED]);
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'El pago fue rechazado por el banco. Código: ' . $response->getResponseCode(),
                ]);
            }

            // ── Pago exitoso: actualizar orden y descontar stock ──────────────
            DB::transaction(function () use ($order, $response) {
                $order->update([
                    'status'                       => Order::STATUS_PAID,
                    'transbank_authorization_code' => $response->getAuthorizationCode(),
                    'transbank_transaction_date'   => $response->getTransactionDate(),
                ]);

                foreach ($order->items()->with('product')->get() as $item) {
                    $item->product->decrement('stock', $item->quantity);
                }
            });

            return response()->json([
                'status'           => 'paid',
                'order_id'         => $order->id,
                'authorization_code' => $response->getAuthorizationCode(),
                'message'          => '¡Pago confirmado! Tu pedido está en proceso.',
            ]);

        } catch (\Exception $e) {
            Log::error('CheckoutController@return Transbank error: ' . $e->getMessage(), [
                'token' => $token,
                'order_id' => $order->id,
            ]);

            $order->update(['status' => Order::STATUS_FAILED]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al confirmar el pago. Contacta soporte con tu orden #' . $order->id,
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

    private function buildTransaction(): Transaction
    {
        $env = config('transbank.env') === 'production'
            ? Options::ENVIRONMENT_PRODUCTION
            : Options::ENVIRONMENT_INTEGRATION;

        return new Transaction(new Options(
            config('transbank.commerce_code'),
            config('transbank.api_key'),
            $env
        ));
    }
}
