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
                        ->default(fn () => Setting::where('key', 'google_sheets_csv_url')->value('value'))
                ])
                ->action(function (array $data) {
                    Setting::updateOrCreate(
                        ['key' => 'google_sheets_csv_url'],
                        ['value' => $data['csv_url'], 'label' => 'URL Google Sheets', 'type' => 'text']
                    );
                    
                    $this->runSync($data['csv_url']);
                }),
        ];
    }

    protected function runSync($url)
    {
        try {
            $response = Http::get($url);
            if (!$response->successful()) {
                Notification::make()->title('Error al descargar el CSV de la URL.')->danger()->send();
                return;
            }
            
            $csv = $response->body();
            // Soporte para saltos de línea y parseo seguro con str_getcsv
            $lines = explode(PHP_EOL, $csv);
            
            if (count($lines) < 2) {
                Notification::make()->title('El archivo parece estar vacío.')->warning()->send();
                return;
            }

            // Normalizar encabezados (quitar acentos, tildes y pasar a minusculas)
            $rawHeader = str_getcsv(array_shift($lines));
            $header = array_map(function($h) {
                $h = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($h)));
                return preg_replace('/[^a-z0-9]/', '_', $h);
            }, $rawHeader);
            
            $groupedProducts = [];

            foreach ($lines as $line) {
                if (trim($line) === '') continue;
                $row = str_getcsv($line);
                if (count($row) < count($header)) continue;
                
                $data = array_combine($header, $row);
                
                $sku = trim($data['sku'] ?? '');
                if (empty($sku)) continue;

                // Buscar columnas variables por coincidencia parcial
                $condicionKey = collect(array_keys($data))->first(fn($k) => str_contains($k, 'condici'));
                $garantiaKey = collect(array_keys($data))->first(fn($k) => str_contains($k, 'garant'));
                $origenKey = collect(array_keys($data))->first(fn($k) => str_contains($k, 'origen'));

                // Si es primera vez que vemos el SKU, guardamos los datos base
                if (!isset($groupedProducts[$sku])) {
                    $groupedProducts[$sku] = [
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
                        'modelos' => []
                    ];
                }

                // Extraer modelos compatibles para este SKU
                $marcaCompatible = trim($data['marca_compatible'] ?? '');
                $modeloCompatible = trim($data['modelo_compatible'] ?? '');
                $cilindrada = trim($data['cilindrada'] ?? '');
                $anios = trim($data['anos_compatibles'] ?? '');

                if (!empty($marcaCompatible) && !empty($modeloCompatible)) {
                    $groupedProducts[$sku]['modelos'][] = [
                        'marca' => $marcaCompatible,
                        'modelo' => $modeloCompatible,
                        'cilindrada' => $cilindrada,
                        'anios' => $anios
                    ];
                }
            }
            
            $count = 0;
            foreach ($groupedProducts as $sku => $productData) {
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

                // 4. Construir Descripción Extendida
                $descExtra = [];
                if (!empty($productData['origen'])) $descExtra[] = "Origen: " . $productData['origen'];
                if (!empty($productData['condicion'])) $descExtra[] = "Condición: " . $productData['condicion'];
                if (!empty($productData['garantia'])) $descExtra[] = "Garantía: " . $productData['garantia'];

                $cilindradas = array_filter(array_column($productData['modelos'], 'cilindrada'));
                if (!empty($cilindradas)) {
                    $descExtra[] = "Cilindradas compatibles: " . implode(', ', array_unique($cilindradas));
                }

                $finalDescription = $productData['descripcion'] ?? '';
                if (!empty($descExtra)) {
                    $finalDescription .= "\n\n" . implode("\n", $descExtra);
                }

                // 5. Crear o Actualizar Producto
                $productAttributes = [
                    'name' => $productData['producto'] ?: 'Sin nombre',
                    'slug' => \Illuminate\Support\Str::slug(($productData['producto'] ?: 'producto') . '-' . $sku),
                    'description' => trim($finalDescription),
                    'regular_price' => $regularPrice,
                    'wholesale_price' => $wholesalePrice,
                    'stock' => $stock,
                    'brand_id' => $brandId,
                    'category_id' => $catId,
                ];

                $product = Product::withTrashed()->where('sku', $sku)->first();
                
                if ($product) {
                    $product->update($productAttributes);
                    if ($product->trashed()) {
                        $product->restore();
                    }
                } else {
                    $product = Product::create(array_merge(['sku' => $sku], $productAttributes));
                }

                // 6. Vincular Modelos Compatibles
                $carModelIdsToSync = [];
                foreach ($productData['modelos'] as $mod) {
                    // Buscar o crear la Marca de Auto (Chery, Great Wall, etc)
                    $carBrandSlug = \Illuminate\Support\Str::slug($mod['marca']);
                    $carBrand = Brand::firstOrCreate(
                        ['slug' => $carBrandSlug],
                        ['name' => $mod['marca'], 'is_active' => true]
                    );

                    // Parsear años (ej: "2016 - 2017")
                    $yearStart = null;
                    $yearEnd = null;
                    if (!empty($mod['anios'])) {
                        preg_match_all('/\d{4}/', $mod['anios'], $matches);
                        if (count($matches[0]) >= 2) {
                            $yearStart = $matches[0][0];
                            $yearEnd = $matches[0][1];
                        } elseif (count($matches[0]) == 1) {
                            $yearStart = $matches[0][0];
                        }
                    }

                    // Buscar o crear el Modelo de Auto buscando por SLUG, no por nombre
                    $carModelSlug = \Illuminate\Support\Str::slug($mod['marca'] . '-' . $mod['modelo']);
                    $carModel = \App\Models\CarModel::firstOrCreate(
                        ['slug' => $carModelSlug],
                        [
                            'name' => $mod['modelo'],
                            'brand_id' => $carBrand->id,
                            'is_active' => true,
                            'year_start' => $yearStart,
                            'year_end' => $yearEnd
                        ]
                    );
                    
                    // Si el excel tiene años específicos y el modelo existente los tenía nulos, los actualizamos
                    if ($yearStart && !$carModel->year_start) {
                        $carModel->update(['year_start' => $yearStart, 'year_end' => $yearEnd]);
                    }

                    $carModelIdsToSync[] = $carModel->id;
                }

                if (!empty($carModelIdsToSync)) {
                    $product->carModels()->sync(array_unique($carModelIdsToSync));
                }

                $count++;
            }
            
            Notification::make()->title("¡Éxito! Se procesaron $count productos correctamente.")->success()->send();
            
        } catch (\Exception $e) {
            Notification::make()->title('Error interno: ' . $e->getMessage())->danger()->send();
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
                    ->directory('temp_sync_images')
                    ->preserveFilenames()
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
        
        foreach ($data['images'] as $tempFile) {
            if (is_string($tempFile)) {
                $absolutePath = storage_path('app/public/' . $tempFile);
                $filename = basename($tempFile);
            } else if (is_object($tempFile) && method_exists($tempFile, 'getRealPath')) {
                $absolutePath = $tempFile->getRealPath();
                $filename = $tempFile->getClientOriginalName();
            } else {
                continue;
            }

            if (!file_exists($absolutePath)) continue;
            
            $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
            
            $isGallery = false;
            $sku = $nameWithoutExt;
            
            // Verificar si es una imagen de galería (ej. SKU_1)
            if (str_contains($nameWithoutExt, '_')) {
                $parts = explode('_', $nameWithoutExt);
                $suffix = array_pop($parts);
                $potentialSku = implode('_', $parts);
                
                if (Product::where('sku', $potentialSku)->exists()) {
                    $sku = $potentialSku;
                    $isGallery = true;
                }
            }
            
            $product = Product::where('sku', $sku)->first();
            if (!$product) {
                $notFound++;
                continue;
            }
            
            try {
                // PROCESAMIENTO NATIVO CON PHP GD (Sin librerías externas)
                $info = @getimagesize($absolutePath);
                if (!$info) continue;

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
                        continue 2; // Formato no soportado
                }

                if (!$image) continue;

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
                
                // Actualizar producto en BD (Imagen Principal vs Galería)
                if ($isGallery) {
                    $gallery = is_array($product->gallery) ? $product->gallery : [];
                    $gallery[] = $newFilename;
                    $product->update(['gallery' => array_values(array_unique($gallery))]);
                } else {
                    $product->update(['image' => $newFilename]);
                }
                
                $processed++;
                
            } catch (\Throwable $e) {
                // Captura Error o Exception genérico
                continue;
            }
        }
        
        $this->form->fill(); // Limpiar el formulario
        
        $msg = "Se transformaron y asignaron $processed imágenes exitosamente.";
        if ($notFound > 0) {
            $msg .= " Nota: $notFound imágenes se ignoraron porque no existe ningún producto con ese nombre (SKU).";
        }
        
        Notification::make()->title('Optimización de Imágenes Lista')->body($msg)->success()->send();
    }
}
