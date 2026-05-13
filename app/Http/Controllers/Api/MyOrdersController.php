<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyOrdersController extends Controller
{
    /**
     * GET /api/my-orders
     * Lista todas las órdenes del usuario autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ]
        ]);
    }

    /**
     * GET /api/my-orders/{id}
     * Detalle de una orden específica (si pertenece al usuario).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::with(['items.product'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }
}
