<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use App\Models\Setting;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductSync extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?string $navigationLabel = 'Sincronizar Productos';
    protected static ?string $title = 'Sincronización y Carga Masiva';

    protected static string $view = 'filament.pages.product-sync';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncGoogleSheets')
                ->label('Sincronizar desde Excel/CSV')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\TextInput::make('csv_url')
                        ->label('URL Pública del CSV (Google Sheets)')
                        ->helperText('Debes publicar el Google Sheet en la web como formato CSV y pegar el link aquí.')
                        ->required()
                        ->default(function () {
                            // Blindado: si la tabla 'settings' no existe o la BD falla,
                            // no debe reventar el montaje de la acción (error 500 al abrir el modal).
                            try {
                                if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                                    return null;
                                }
                                return Setting::where('key', 'google_sheets_csv_url')->value('value');
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::warning('ProductSync default csv_url: ' . $e->getMessage());
                                return null;
                            }
                        })
                ])
                ->action(function (array $data) {
                    try {
                        Setting::updateOrCreate(
                            ['key' => 'google_sheets_csv_url'],
                            ['value' => $data['csv_url'], 'label' => 'URL Google Sheets', 'type' => 'text']
                        );
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('ProductSync guardar URL: ' . $e->getMessage());
                    }

                    $this->runSync($data['csv_url']);
                }),
        ];
    }

    protected function runSync($url)
    {
        // Una carga masiva puede superar el límite por defecto de PHP en cPanel (30s / 128M).
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        // catch (\Throwable) captura además \Error, \TypeError y \ValueError
        // (p. ej. array_combine con filas mal formadas) que antes provocaban un 500 crudo.
        try {
            $response = Http::timeout(120)
                ->withOptions(['allow_redirects' => true])
                ->get($url);

            if (!$response->successful()) {
                Notification::make()->title('Error al descargar el CSV de la URL (HTTP ' . $response->status() . ').')->danger()->send();
                return;
            }

            $csv = $response->body();

            // El usuario suele pegar un enlace de carpeta de Google Drive o el enlace
            // normal de la hoja (que devuelven una página HTML, no un CSV).
            $contentType = strtolower($response->header('Content-Type') ?? '');
            $looksLikeHtml = str_contains($contentType, 'text/html')
                || preg_match('/^\s*<(?:!doctype|html|head|body)/i', $csv);

            if ($looksLikeHtml) {
                Notification::make()
                    ->title('Ese enlace no es un CSV')
                    ->body('Parece un enlace normal de Google Drive/Sheets. En la hoja de cálculo ve a '
                        . 'Archivo → Compartir → Publicar en la web → formato "Valores separados por comas (.csv)" '
                        . 'y pega esa URL (termina en /pub?output=csv o /export?format=csv).')
                    ->danger()
                    ->persistent()
                    ->send();
                return;
            }

            // Normalizar TODO tipo de salto de línea (Windows \r\n, Mac clásico \r, Unix \n).
            // Antes se usaba explode(PHP_EOL) que en el servidor sólo corta por \n
            // y dejaba un \r colgando (o no cortaba nada con archivos \r).
            $lines = preg_split('/\r\n|\r|\n/', $csv);

            if (!is_array($lines) || count(array_filter($lines, fn ($l) => trim($l) !== '')) < 2) {
                Notification::make()->title('El archivo parece estar vacío.')->warning()->send();
                return;
            }

            // Normalizar encabezados (quitar acentos, tildes y pasar a minusculas)
            $rawHeader = str_getcsv(array_shift($lines), ',', '"', '');
            $header = array_map(function ($h) {
                $h = trim((string) $h);
                $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $h);
                if ($ascii === false) {
                    // Fallback si iconv//TRANSLIT no está disponible en el servidor
                    $ascii = preg_replace('/[^\x20-\x7E]/', '', $h);
                }
                return preg_replace('/[^a-z0-9]/', '_', strtolower($ascii));
            }, $rawHeader);

            $headerCount = count($header);

            // Sin columna SKU no se puede importar nada (probable link equivocado o
            // separador distinto a la coma).
            if (!in_array('sku', $header, true)) {
                Notification::make()
                    ->title('No se encontró la columna "SKU"')
                    ->body('Encabezados detectados: ' . implode(', ', array_slice($header, 0, 20))
                        . '. Verifica que el enlace sea el CSV publicado de la hoja correcta y que use coma como separador.')
                    ->danger()
                    ->persistent()
                    ->send();
                return;
            }

            // UN PRODUCTO POR FILA: dos filas con el mismo SKU pero distinto vehículo
            // compatible son dos productos distintos. La clave de identidad para
            // re-importar es sku + marca compatible + modelo compatible.
            $rows = [];

            foreach ($lines as $line) {
                if (trim($line) === '') continue;
                $row = str_getcsv($line, ',', '"', '');

                // Ajustar la fila EXACTAMENTE al ancho del encabezado:
                // - si faltan columnas se rellenan con '' (antes se descartaba la fila entera)
                // - si sobran columnas se recortan (antes array_combine lanzaba ValueError -> 500)
                if (count($row) < $headerCount) {
                    $row = array_pad($row, $headerCount, '');
                } elseif (count($row) > $headerCount) {
                    $row = array_slice($row, 0, $headerCount);
                }

                $data = array_combine($header, $row);

                $sku = trim($data['sku'] ?? '');
                if (empty($sku)) continue;

                // Buscar columnas variables por coincidencia parcial
                $condicionKey = collect(array_keys($data))->first(fn($k) => str_contains($k, 'condici'));
                $garantiaKey = collect(array_keys($data))->first(fn($k) => str_contains($k, 'garant'));
                $origenKey = collect(array_keys($data))->first(fn($k) => str_contains($k, 'origen'));

                $marcaCompatible = trim($data['marca_compatible'] ?? '');
                $modeloCompatible = trim($data['modelo_compatible'] ?? '');
                $cilindrada = trim($data['cilindrada'] ?? '');
                $anios = trim($data['anos_compatibles'] ?? '');

                $importKey = $sku . '|'
                    . \Illuminate\Support\Str::slug($marcaCompatible) . '|'
                    . \Illuminate\Support\Str::slug($modeloCompatible);

                // Si la misma clave aparece varias veces en el archivo, la última fila
                // gana en los campos escalares (nombre, precio, stock...).
                $rows[$importKey] = [
                    'sku' => $sku,
                    'import_key' => $importKey,
                    'producto' => $data['producto'] ?? '',
                    'descripcion' => $data['descripcion'] ?? '',
                    'categoria' => $data['categoria'] ?? '',
                    'marca_repuesto' => $data['marca_del_repuesto'] ?? '',
                    'precio_venta' => $data['precio_venta'] ?? '0',
                    'precio_oferta' => $data['precio_oferta'] ?? '0',
                    'stock_actual' => $data['stock_actual'] ?? '0',
                    'condicion' => $condicionKey ? trim($data[$condicionKey]) : '',
                    'garantia' => $garantiaKey ? trim($data[$garantiaKey]) : '',
                    'origen' => $origenKey ? trim($data[$origenKey]) : '',
                    'marca_compatible' => $marcaCompatible,
                    'modelo_compatible' => $modeloCompatible,
                    'cilindrada' => $cilindrada,
                    'anios' => $anios,
                ];
            }

            if (empty($rows)) {
                Notification::make()
                    ->title('No se importó ningún producto')
                    ->body('Se leyó el archivo pero ninguna fila tenía un SKU válido. '
                        . 'Revisa que la hoja publicada sea la correcta y tenga datos bajo la columna SKU.')
                    ->warning()
                    ->persistent()
                    ->send();
                return;
            }

            $created = 0;
            $updated = 0;
            $usedSlugs = [];
            $adoptedLegacyIds = [];

            foreach ($rows as $productData) {
                $sku = $productData['sku'];

                // 1. Marca del Repuesto
                $brandId = null;
                if (!empty($productData['marca_repuesto'])) {
                    $brandSlug = \Illuminate\Support\Str::slug($productData['marca_repuesto']);
                    $brand = Brand::firstOrCreate(
                        ['slug' => $brandSlug],
                        ['name' => $productData['marca_repuesto'], 'is_active' => true]
                    );
                    $brandId = $brand->id;
                }

                // 2. Categoría
                $catId = null;
                if (!empty($productData['categoria'])) {
                    $catSlug = \Illuminate\Support\Str::slug($productData['categoria']);
                    $cat = Category::firstOrCreate(
                        ['slug' => $catSlug],
                        ['name' => $productData['categoria'], 'is_active' => true]
                    );
                    $catId = $cat->id;
                }

                // 3. Precios y Stock
                $regularPrice = (int) preg_replace('/[^0-9]/', '', $productData['precio_venta']);
                $wholesalePrice = (int) preg_replace('/[^0-9]/', '', $productData['precio_oferta']);
                $stock = (int) preg_replace('/[^0-9]/', '', $productData['stock_actual']);

                // 4. Vehículo compatible de ESTA fila (uno por producto)
                $marcaCompatible = $productData['marca_compatible'];
                $modeloCompatible = $productData['modelo_compatible'];
                $vehiculo = trim($marcaCompatible . ' ' . $modeloCompatible);

                // 5. Nombre visible: se agrega el vehículo para diferenciar las fichas
                //    cuando el mismo SKU se repite para varios autos.
                $baseName = trim($productData['producto']) ?: 'Sin nombre';
                $displayName = $vehiculo !== '' ? ($baseName . ' — ' . $vehiculo) : $baseName;

                // 6. Descripción extendida
                $descExtra = [];
                if (!empty($productData['origen']))    $descExtra[] = "Origen: " . $productData['origen'];
                if (!empty($productData['condicion'])) $descExtra[] = "Condición: " . $productData['condicion'];
                if (!empty($productData['garantia']))  $descExtra[] = "Garantía: " . $productData['garantia'];
                if (!empty($productData['cilindrada'])) $descExtra[] = "Cilindrada: " . $productData['cilindrada'];
                if ($vehiculo !== '')                   $descExtra[] = "Compatible con: " . $vehiculo
                    . (!empty($productData['anios']) ? " ({$productData['anios']})" : '');

                $finalDescription = trim($productData['descripcion'] ?? '');
                if (!empty($descExtra)) {
                    $finalDescription = trim($finalDescription . "\n\n" . implode("\n", $descExtra));
                }

                // 7. Buscar el producto por su clave de importación (NO por SKU: el SKU
                //    puede repetirse).
                $product = Product::withTrashed()->where('import_key', $productData['import_key'])->first();

                // 7b. Primera sincronización tras habilitar SKU duplicado: adoptar en su
                //     lugar un producto heredado (mismo SKU, todavía sin import_key) en
                //     vez de crear uno nuevo y dejar el viejo huérfano. Sólo la primera
                //     fila de cada SKU lo adopta; las siguientes crean productos nuevos.
                if (! $product) {
                    $legacy = Product::withTrashed()
                        ->where('sku', $sku)
                        ->whereNull('import_key')
                        ->orderBy('id')
                        ->first();
                    if ($legacy && ! in_array($legacy->id, $adoptedLegacyIds, true)) {
                        $product = $legacy;
                        $adoptedLegacyIds[] = $legacy->id;
                    }
                }

                // 8. Slug único y estable por fila
                $slugBase = \Illuminate\Support\Str::slug($baseName . '-' . $sku . ($modeloCompatible !== '' ? '-' . $modeloCompatible : ''));
                $slug = $slugBase;
                $n = 2;
                while (
                    isset($usedSlugs[$slug])
                    || Product::withTrashed()
                        ->where('slug', $slug)
                        ->when($product, fn ($q) => $q->where('id', '!=', $product->id))
                        ->exists()
                ) {
                    $slug = $slugBase . '-' . $n++;
                }
                $usedSlugs[$slug] = true;

                $productAttributes = [
                    'name'           => $displayName,
                    'slug'           => $slug,
                    'description'    => $finalDescription,
                    'regular_price'  => $regularPrice,
                    'wholesale_price'=> $wholesalePrice,
                    'stock'          => $stock,
                    'brand_id'       => $brandId,
                    'category_id'    => $catId,
                    'import_key'     => $productData['import_key'],
                ];

                if ($product) {
                    $product->update($productAttributes);
                    if ($product->trashed()) {
                        $product->restore();
                    }
                    $updated++;
                } else {
                    $product = Product::create(array_merge(['sku' => $sku], $productAttributes));
                    $created++;
                }

                // 9. Vincular el modelo compatible de esta fila
                if ($marcaCompatible !== '' && $modeloCompatible !== '') {
                    $carBrand = Brand::firstOrCreate(
                        ['slug' => \Illuminate\Support\Str::slug($marcaCompatible)],
                        ['name' => $marcaCompatible, 'is_active' => true]
                    );

                    $yearStart = null;
                    $yearEnd = null;
                    if (!empty($productData['anios'])) {
                        preg_match_all('/\d{4}/', $productData['anios'], $matches);
                        if (count($matches[0]) >= 2) {
                            $yearStart = $matches[0][0];
                            $yearEnd = $matches[0][1];
                        } elseif (count($matches[0]) === 1) {
                            $yearStart = $matches[0][0];
                        }
                    }

                    $carModel = \App\Models\CarModel::firstOrCreate(
                        ['slug' => \Illuminate\Support\Str::slug($marcaCompatible . '-' . $modeloCompatible)],
                        [
                            'name' => $modeloCompatible,
                            'brand_id' => $carBrand->id,
                            'is_active' => true,
                            'year_start' => $yearStart,
                            'year_end' => $yearEnd,
                        ]
                    );

                    if ($yearStart && !$carModel->year_start) {
                        $carModel->update(['year_start' => $yearStart, 'year_end' => $yearEnd]);
                    }

                    $product->carModels()->sync([$carModel->id]);
                } else {
                    $product->carModels()->sync([]);
                }
            }

            Notification::make()
                ->title('Sincronización completa')
                ->body("Creados: {$created} · Actualizados: {$updated} · Total de filas: " . count($rows))
                ->success()
                ->send();

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ProductSync runSync: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            Notification::make()
                ->title('Error interno durante la sincronización')
                ->body(\Illuminate\Support\Str::limit($e->getMessage(), 300))
                ->danger()
                ->persistent()
                ->send();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('images')
                    ->label('Imágenes (Formatos: JPG, PNG, WEBP, etc.)')
                    ->helperText('Para la imagen principal usa el SKU (ej. FRENOS-001.jpg). Para imágenes secundarias (galería), usa el SKU seguido de un guión bajo y un número (ej. FRENOS-001_1.jpg, FRENOS-001_2.jpg)')
                    ->multiple()
                    ->storeFiles(false)
                    ->columnSpanFull()
            ])
            ->statePath('data');
    }

    public function processImages()
    {
        $data = $this->form->getState();
        if (empty($data['images'])) {
            Notification::make()->title('No subiste ninguna imagen.')->warning()->send();
            return;
        }

        if (!function_exists('imagewebp')) {
            Notification::make()->title('Tu servidor (cPanel) no tiene activa la extensión PHP GD (imagewebp).')->danger()->send();
            return;
        }

        $processed = 0;
        $notFound = 0;
        $errors = [];
        
        foreach ($data['images'] as $tempFile) {
            $filename = 'Desconocido';
            if (is_string($tempFile)) {
                // Posible ruta relativa
                $absolutePath = storage_path('app/public/' . $tempFile);
                if (!file_exists($absolutePath)) {
                    $absolutePath2 = storage_path('app/livewire-tmp/' . $tempFile);
                    if (file_exists($absolutePath2)) {
                        $absolutePath = $absolutePath2;
                    } else {
                        // Podría estar en storage/app/ directamente
                        $absolutePath3 = storage_path('app/' . $tempFile);
                        if (file_exists($absolutePath3)) {
                            $absolutePath = $absolutePath3;
                        }
                    }
                }
                $filename = basename($tempFile);
            } else if (is_object($tempFile) && method_exists($tempFile, 'getRealPath')) {
                $absolutePath = $tempFile->getRealPath();
                $filename = $tempFile->getClientOriginalName();
            } else {
                $errors[] = "Formato de archivo no reconocido por Livewire.";
                continue;
            }

            if (!file_exists($absolutePath)) {
                $errors[] = "El archivo físico no se encontró en el disco: " . $absolutePath;
                continue;
            }
            
            $nameWithoutExt = trim(pathinfo($filename, PATHINFO_FILENAME));
            
            $isGallery = false;
            $sku = $nameWithoutExt;
            
            // Verificar si es una imagen de galería (ej. SKU_1)
            if (str_contains($nameWithoutExt, '_')) {
                $parts = explode('_', $nameWithoutExt);
                $suffix = array_pop($parts);
                $potentialSku = trim(implode('_', $parts));
                
                if (Product::where('sku', $potentialSku)->exists()) {
                    $sku = $potentialSku;
                    $isGallery = true;
                }
            }
            
            // El SKU puede corresponder a varios productos (uno por vehículo compatible):
            // la misma foto se asigna a todos.
            $products = Product::where('sku', $sku)->get();
            if ($products->isEmpty()) {
                $notFound++;
                continue;
            }
            
            try {
                // PROCESAMIENTO NATIVO CON PHP GD (Sin librerías externas)
                $info = @getimagesize($absolutePath);
                if (!$info) {
                    $errors[] = "getimagesize falló o la imagen es corrupta: " . $filename;
                    continue;
                }

                $mime = $info['mime'];
                switch ($mime) {
                    case 'image/jpeg':
                        $image = @imagecreatefromjpeg($absolutePath);
                        break;
                    case 'image/png':
                        $image = @imagecreatefrompng($absolutePath);
                        break;
                    case 'image/webp':
                        $image = @imagecreatefromwebp($absolutePath);
                        break;
                    default:
                        $errors[] = "Formato mime no soportado ($mime) en: " . $filename;
                        continue 2; // Formato no soportado
                }

                if (!$image) {
                    $errors[] = "No se pudo decodificar la imagen (falta memoria o está corrupta): " . $filename;
                    continue;
                }

                $width = imagesx($image);
                $height = imagesy($image);

                // Escalar si es muy grande
                if ($width > 1200) {
                    $newWidth = 1200;
                    $newHeight = (int) (($height / $width) * 1200);
                    $newImage = imagecreatetruecolor($newWidth, $newHeight);
                    
                    // Mantener transparencia si es PNG o WEBP
                    if ($mime === 'image/png' || $mime === 'image/webp') {
                        imagealphablending($newImage, false);
                        imagesavealpha($newImage, true);
                        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
                    }

                    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagedestroy($image);
                    $image = $newImage;
                }
                
                // Generar ruta y convertir a WebP (calidad 80)
                $newFilename = 'products/' . $sku . '-' . uniqid() . '.webp';
                $destPath = storage_path('app/public/' . $newFilename);
                
                if (!file_exists(dirname($destPath))) {
                    mkdir(dirname($destPath), 0755, true);
                }
                
                imagewebp($image, $destPath, 80);
                imagedestroy($image);
                
                // Actualizar TODOS los productos con este SKU (Imagen Principal vs Galería)
                foreach ($products as $product) {
                    if ($isGallery) {
                        $gallery = is_array($product->gallery) ? $product->gallery : [];
                        $gallery[] = $newFilename;
                        $product->update(['gallery' => array_values(array_unique($gallery))]);
                    } else {
                        $product->update(['image' => $newFilename]);
                    }
                }

                $processed++;
                
            } catch (\Throwable $e) {
                $errors[] = "Error crítico procesando $filename: " . $e->getMessage();
                continue;
            }
        }
        
        $msg = "Se transformaron y asignaron {$processed} imágenes exitosamente.";
        if ($notFound > 0) {
            $msg .= " Sin embargo, {$notFound} imágenes fueron ignoradas porque su nombre no coincidía exactamente con ningún SKU.";
            if (empty($errors)) {
                Notification::make()->title('Proceso Completado con advertencias')->body($msg)->warning()->send();
            }
        }
        
        if (count($errors) > 0) {
            // Mostrar los errores reales técnicos al usuario
            Notification::make()->title('Hubo errores técnicos al procesar')->body(implode(' | ', $errors))->danger()->send();
        } else if ($processed > 0 && $notFound == 0) {
            Notification::make()->title('Proceso Completado')->body($msg)->success()->send();
        }
        
        $this->form->fill(['images' => []]);
    }
}
