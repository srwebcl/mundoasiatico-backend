<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * POST /api/coupons/apply
     * Valida un cupón y devuelve el descuento aplicable.
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code'   => 'required|string',
            'amount' => 'required|integer|min:1',
        ]);

        $coupon = Coupon::valid()
            ->where('code', strtoupper($request->code))
            ->first();

        if (! $coupon) {
            return response()->json([
                'message' => 'El cupón ingresado no es válido o ha expirado.',
            ], 422);
        }

        if ($request->amount < $coupon->min_amount) {
            return response()->json([
                'message' => "Este cupón requiere una compra mínima de $" . number_format($coupon->min_amount, 0, ',', '.'),
            ], 422);
        }

        $discount = $coupon->calculateDiscount($request->amount);

        return response()->json([
            'message'  => '¡Cupón aplicado con éxito!',
            'code'     => $coupon->code,
            'discount' => $discount,
            'total'    => max(0, $request->amount - $discount),
        ]);
    }
}
