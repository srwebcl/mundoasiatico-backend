<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * Listado paginado con filtros dinámicos.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->active()
            ->with(['category', 'brand']);

        // ── Filtro por categoría (slug o id) ─────────────────────────────────
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category)
                  ->orWhere('id', $request->category);
            });
        }

        // ── Filtro por marca del VEHÍCULO (slug o id) ─────────────────────────
        if ($request->filled('brand')) {
            $query->whereHas('carModels.brand', function ($q) use ($request) {
                $q->where('slug', $request->brand)
                  ->orWhere('id', $request->brand);
            });
        }

        // ── Filtro por modelo de auto (slug o id) ─────────────────────────────
        if ($request->filled('car_model')) {
            $query->whereHas('carModels', function ($q) use ($request) {
                $q->where('slug', $request->car_model)
                  ->orWhere('id', $request->car_model);
            });
        }

        // ── Filtro por precio ─────────────────────────────────────────────────
        if ($request->filled('price_min')) {
            $query->where('regular_price', '>=', (int) $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('regular_price', '<=', (int) $request->price_max);
        }

        // ── Búsqueda por texto ────────────────────────────────────────────────
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', $term)
                  ->orWhere('sku', 'LIKE', $term)
                  ->orWhere('description', 'LIKE', $term);
            });
        }

        // ── Ordenamiento ──────────────────────────────────────────────────────
        $sort = $request->get('sort', 'default');
        match ($sort) {
            'price-asc'  => $query->orderBy('regular_price', 'asc'),
            'price-desc' => $query->orderBy('regular_price', 'desc'),
            'name-asc'   => $query->orderBy('name', 'asc'),
            'name-desc'  => $query->orderBy('name', 'desc'),
            'featured'   => $query->orderByDesc('is_featured')->orderBy('name'),
            default      => $query->orderByDesc('is_featured')->orderByDesc('created_at'),
        };

        $products = $query->paginate(12)->withQueryString();

        return response()->json([
            'data'  => ProductListResource::collection($products),
            'meta'  => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
            'links' => [
                'next' => $products->nextPageUrl(),
                'prev' => $products->previousPageUrl(),
            ],
        ]);
    }

    /**
     * GET /api/products/{slug}
     * Detalle completo del producto.
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'brand', 'carModels.brand'])
            ->firstOrFail();

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }
}
