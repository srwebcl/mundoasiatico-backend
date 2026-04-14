<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    /**
     * GET /api/brands
     * Listado de marcas activas con sus modelos de autos anidados.
     * Usado por el frontend para poblar los selectores Marca → Modelo.
     */
    public function index(): JsonResponse
    {
        $brands = Brand::where('is_active', true)
            ->with(['carModels' => function ($q) {
                $q->where('is_active', true)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => BrandResource::collection($brands),
        ]);
    }
}
