<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * GET /api/categories
     * Listado de categorías activas con conteo de productos.
     */
    public function index(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }
}
