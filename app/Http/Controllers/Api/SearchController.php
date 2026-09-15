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

        // Palabras del término (sin las de relleno: "de", "para", etc.), para que
        // "gran tiggo chery" o "modelo tiggo de chery" encuentren "Chery Grand Tiggo"
        // sin importar el orden (misma lógica que Product::scopeSearch).
        $words = Product::searchWords($term);

        // 1. Buscar Modelos de Autos (unidos a su marca)
        $carModels = CarModel::with('brand');
        foreach ($words as $word) {
            $like = '%' . $word . '%';
            $carModels->where(function ($q) use ($like) {
                $q->where('name', 'LIKE', $like)
                  ->orWhereHas('brand', fn ($q2) => $q2->where('name', 'LIKE', $like));
            });
        }
        $carModels = $carModels->take(5)->get()->map(function ($car) {
            return [
                'id'    => $car->id,
                'name'  => $car->name,
                'slug'  => $car->slug,
                'brand' => $car->brand ? $car->brand->name : null,
            ];
        });

        // 2. Buscar Categorías
        $categories = Category::where('name', 'LIKE', '%' . $term . '%')
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

        // 3. Buscar Repuestos (Productos) — misma búsqueda "inteligente" multi-palabra
        // que usa el filtro del catálogo (Product::scopeSearch), para que ambos
        // buscadores del sitio se comporten igual y reflejen siempre el catálogo actual.
        $products = Product::where('is_active', true)
            ->search($term)
            ->with(['category', 'brand', 'carModels.brand'])
            ->take(6)
            ->get();

        return response()->json([
            'car_models' => $carModels,
            'categories' => $categories,
            'products'   => ProductListResource::collection($products),
        ]);
    }
}
