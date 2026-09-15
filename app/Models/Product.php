<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'import_key',
        'name',
        'slug',
        'description',
        'regular_price',
        'wholesale_price',
        'stock',
        'image',
        'is_active',
        'is_featured',
        'category_id',
        'brand_id',
        'gallery',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'regular_price'   => 'integer',
            'wholesale_price' => 'integer',
            'stock'           => 'integer',
            'is_active'       => 'boolean',
            'is_featured'     => 'boolean',
            'gallery'         => 'array',
        ];
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Palabras de relleno del español que la gente escribe de forma natural
     * ("bujías PARA chery", "filtro DE aceite") pero que no aparecen en ningún
     * producto. Si se buscaran igual que el resto, una sola de estas palabras
     * bastaría para que la búsqueda completa no encuentre nada.
     */
    private static array $stopwords = [
        'para', 'de', 'del', 'la', 'el', 'los', 'las', 'un', 'una', 'unos', 'unas',
        'y', 'o', 'con', 'en', 'que', 'por', 'al', 'a', 'su',
    ];

    /**
     * Separa un término de búsqueda en palabras "con sentido", quitando las de
     * relleno. Si no queda ninguna (ej. el usuario solo escribió "de"), se usan
     * las palabras originales para no devolver 0 resultados por accidente.
     */
    public static function searchWords(?string $term): array
    {
        $words = preg_split('/[\s,]+/', trim((string) $term), -1, PREG_SPLIT_NO_EMPTY);
        $meaningful = array_values(array_filter(
            $words,
            fn ($w) => ! in_array(mb_strtolower($w), self::$stopwords, true)
        ));

        return $meaningful ?: $words;
    }

    /**
     * Búsqueda "inteligente" por texto libre: separa el término en palabras y exige
     * que CADA palabra aparezca en algún lugar (nombre, SKU, descripción, categoría,
     * marca del repuesto o marca/modelo del vehículo compatible), sin importar el
     * orden ni en qué campo caiga cada una.
     *
     * Así "chery tiggo 2" encuentra "BUJIAS PUNTA IRIDIUM, CHERY TIGGO 2 JGO" y
     * también "tiggo 2 chery", "bujias chery" o "bujías PARA chery" (antes se
     * exigía una única frase literal, en el mismo orden, en un solo campo).
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $words = static::searchWords($term);

        foreach ($words as $word) {
            $like = '%' . $word . '%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('name', 'LIKE', $like)
                  ->orWhere('sku', 'LIKE', $like)
                  ->orWhere('description', 'LIKE', $like)
                  ->orWhereHas('category', fn (Builder $q2) => $q2->where('name', 'LIKE', $like))
                  ->orWhereHas('brand', fn (Builder $q2) => $q2->where('name', 'LIKE', $like))
                  ->orWhereHas('carModels', function (Builder $q2) use ($like) {
                      $q2->where('name', 'LIKE', $like)
                         ->orWhereHas('brand', fn (Builder $q3) => $q3->where('name', 'LIKE', $like));
                  });
            });
        }

        return $query;
    }

    // =========================================================================
    // Relaciones
    // =========================================================================

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Modelos de auto compatibles (pivote)
     */
    public function carModels()
    {
        return $this->belongsToMany(CarModel::class, 'car_model_product');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
