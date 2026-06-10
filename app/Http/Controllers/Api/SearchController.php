<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarModel;
use App\Models\Category;
use App\Models\Product;
use App\Http\Resources\ProductListResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * GET /api/search/omnibar
     * Búsqueda global para el autocompletado del Navbar.
     */
    public function omnibar(Request $request): JsonResponse
    {
        $term = $request->query('q', '');
        
        if (strlen($term) < 2) {
            return response()->json([
                'car_models' => [],
                'categories' => [],
                'products'   => [],
            ]);
        }

        $likeTerm = '%' . $term . '%';

        // 1. Buscar Modelos de Autos (unidos a su marca)
        $carModels = CarModel::with('brand')
            ->where('name', 'LIKE', $likeTerm)
            ->orWhereHas('brand', function ($q) use ($likeTerm) {
                $q->where('name', 'LIKE', $likeTerm);
            })
            ->take(5)
            ->get()
            ->map(function ($car) {
                return [
                    'id'    => $car->id,
                    'name'  => $car->name,
                    'slug'  => $car->slug,
                    'brand' => $car->brand ? $car->brand->name : null,
                ];
            });

        // 2. Buscar Categorías
        $categories = Category::where('name', 'LIKE', $likeTerm)
            ->where('is_active', true)
            ->take(3)
            ->get()
            ->map(function ($cat) {
                return [
                    'id'   => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ];
            });

        // 3. Buscar Repuestos (Productos)
        $products = Product::where('is_active', true)
            ->where(function ($q) use ($likeTerm) {
                $q->where('name', 'LIKE', $likeTerm)
                  ->orWhere('sku', 'LIKE', $likeTerm)
                  ->orWhere('description', 'LIKE', $likeTerm);
            })
            ->with(['category', 'brand'])
            ->take(6)
            ->get();

        return response()->json([
            'car_models' => $carModels,
            'categories' => $categories,
            'products'   => ProductListResource::collection($products),
        ]);
    }
}
