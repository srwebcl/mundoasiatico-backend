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
            // Google cachea el CSV publicado hasta 5 minutos (cabecera "Cache-Control:
            // private, max-age=300"). Un parámetro único de por sí no elimina ese
            // retraso (el cacheo es del lado de Google, no algo que controlemos), pero
            // evita que un proxy/CDN intermedio adicional reutilice una respuesta vieja.
            $fetchUrl = $url . (str_contains($url, '?') ? '&' : '?') . '_sync=' . time();

            $response = Http::timeout(120)
                ->withHeaders([
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                ])
                ->withOptions(['allow_redirects' => true])
                ->get($fetchUrl);

            $fetchedAt = now()->timezone('America/Santiago')->format('d-m-Y H:i:s');

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

            // Detección tolerante de columnas: los encabezados varían entre planillas
            // ("PRECIO", "Precio Venta", "Valor", ...). Se busca por coincidencia parcial.
            $findKey = function (array $needles, array $exclude = []) use ($header) {
                foreach ($header as $h) {
                    foreach ($exclude as $e) {
                        if ($e !== '' && str_contains($h, $e)) { continue 2; }
                    }
                    foreach ($needles as $n) {
                        if (str_contains($h, $n)) { return $h; }
                    }
                }
                return null;
            };

            $kProducto    = $findKey(['producto', 'nombre', 'articulo', 'item_nombre', 'descripcion_corta'], ['marca']);
            $kDescripcion = $findKey(['descripcion', 'detalle', 'observacion'], ['corta']);
            $kCategoria   = $findKey(['categoria', 'rubro', 'familia']);
            $kMarcaRep    = $findKey(['repuesto', 'marca_oem', 'fabricante'], ['compatible', 'auto', 'vehiculo'])
                            ?? ($findKey(['marca'], ['compatible', 'auto', 'vehiculo']));
            $kPrecioVenta = $findKey(['precio_venta', 'precio_publico', 'precio_normal', 'precio_detalle', 'precio', 'valor', 'pvp'],
                                     ['oferta', 'mayor', 'min', 'costo', 'compra', 'descuento', 'anterior']);
            $kPrecioOferta= $findKey(['precio_oferta', 'oferta', 'precio_mayorista', 'mayorista', 'precio_mayor', 'precio_descuento'],
                                     ['minimo']);
            $kStock       = $findKey(['stock_actual', 'stock', 'existencia', 'inventario', 'disponible', 'cantidad'], ['min', 'reserv']);
            $kCondicion   = $findKey(['condici']);
            $kGarantia    = $findKey(['garant']);
            $kOrigen      = $findKey(['origen', 'procedencia']);
            $kMarcaComp   = $findKey(['marca_compatible', 'marca_auto', 'marca_vehiculo', 'marca_del_auto', 'marca_de_auto']);
            $kModeloComp  = $findKey(['modelo_compatible', 'modelo_auto', 'modelo_vehiculo', 'modelo'], ['marca']);
            $kCilindrada  = $findKey(['cilindrada', 'motorizacion', 'cc']);
            $kAnios       = $findKey(['anos_compatibles', 'anios_compatibles', 'aos_compatibles', 'anos', 'anios', 'aos', 'ano', 'year', 'periodo']);

            // Si sólo hay una columna de precio, sirve para ambos campos.
            $kPrecioVenta = $kPrecioVenta ?: $kPrecioOferta;
            $kPrecioOferta = $kPrecioOferta ?: $kPrecioVenta;

            // ¿La planilla trae columnas dedicadas de vehículo compatible?
            // Si NO, se extrae del texto del nombre: "... COMPATIBLE CON <MARCA> Y <MODELO>".
            $hasDedicatedVehicleCols = ($kMarcaComp !== null || $kModeloComp !== null);

            $extractVehicle = function (string $text): array {
                $t = trim(preg_replace('/\s+/', ' ', $text));
                if (! preg_match('/^(.*?)\bcompatible\s+con\s+(.+)$/i', $t, $m)) {
                    return ['marca' => '', 'modelo' => '', 'nombre' => $t];
                }
                $nombreLimpio = trim($m[1]) !== '' ? trim($m[1]) : $t;
                $resto = trim($m[2]);
                // El primer " Y " separa la marca del modelo (marcas de 2 palabras OK:
                // "GREAT WALL Y HAVAL H3", "GAC GONOW Y WAY 1.3CC").
                if (preg_match('/^(.*?)\s+Y\s+(.+)$/i', $resto, $mm)) {
                    return ['marca' => trim($mm[1]), 'modelo' => trim($mm[2]), 'nombre' => $nombreLimpio];
                }
                return ['marca' => $resto, 'modelo' => '', 'nombre' => $nombreLimpio];
            };

            // Formato "definitivo" acordado con el negocio: NOMBRE = "PRODUCTO, MARCA MODELO".
            // Todo lo que va después de la PRIMERA coma es el vehículo compatible; debe
            // existir (o crearse) en car-models. Para separar marca de modelo se usan las
            // marcas YA registradas en la base (tabla brands), probando primero las de más
            // de una palabra ("Great Wall", "Gac Gonow") para no cortarlas a la mitad.
            $knownBrands = \App\Models\Brand::pluck('name')->filter()->unique()->values()->all();
            usort($knownBrands, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

            $cleanVehicleText = function (string $t): string {
                $t = trim(rtrim(trim($t), " ,;."));
                $t = preg_replace('/^con\s+/i', '', $t);  // "CON CHANGAN S100" -> "CHANGAN S100"
                $t = preg_replace('/\s+jgo$/i', '', $t);  // "CHERY TIGGO 2 JGO" -> "CHERY TIGGO 2" (JGO = "en juego", es del producto)
                return trim($t);
            };

            $startsWithBrand = function (string $text, string $brand): bool {
                $len = mb_strlen($brand);
                if (mb_strtoupper(mb_substr($text, 0, $len)) !== mb_strtoupper($brand)) return false;
                $next = mb_substr($text, $len, 1);
                return $next === '' || !preg_match('/[A-Za-z0-9]/', $next);
            };

            $matchBrandModel = function (string $text) use ($knownBrands, $startsWithBrand): array {
                foreach ($knownBrands as $brand) {
                    if ($startsWithBrand($text, $brand)) {
                        return ['marca' => $brand, 'modelo' => trim(mb_substr($text, mb_strlen($brand)))];
                    }
                }
                // La marca puede no estar al inicio del texto (ej. filas con la medida o la
                // motorización primero: "2.0 TIGGO 250X225X46 CHERY TIGGO"): se busca la marca
                // en cualquier parte y se toma la ÚLTIMA aparición como punto de corte.
                foreach ($knownBrands as $brand) {
                    $pos = mb_strripos($text, $brand);
                    if ($pos !== false) {
                        return ['marca' => $brand, 'modelo' => trim(mb_substr($text, $pos + mb_strlen($brand)))];
                    }
                }
                // Ninguna marca conocida calzó: mejor esfuerzo (primera palabra = marca).
                $parts = preg_split('/\s+/', trim($text), 2);
                return ['marca' => $parts[0] ?? $text, 'modelo' => $parts[1] ?? ''];
            };

            $val = fn (array $data, ?string $key) => $key !== null ? trim((string) ($data[$key] ?? '')) : '';

            // PRIMERA PASADA: datos base por SKU (primer valor NO vacío que aparezca).
            // Estas planillas suelen llenar los datos del producto sólo en la primera
            // fila de cada SKU y dejar en blanco las filas de compatibilidad siguientes.
            $skuBase = [];
            $parsed = [];

            $baseFields = ['producto', 'descripcion', 'categoria', 'marca_repuesto',
                           'precio_venta', 'precio_oferta', 'stock_actual', 'condicion', 'garantia', 'origen'];

            foreach ($lines as $line) {
                if (trim($line) === '') continue;
                $row = str_getcsv($line, ',', '"', '');

                if (count($row) < $headerCount) {
                    $row = array_pad($row, $headerCount, '');
                } elseif (count($row) > $headerCount) {
                    $row = array_slice($row, 0, $headerCount);
                }

                $data = array_combine($header, $row);
                $sku = trim($data['sku'] ?? '');
                if ($sku === '') continue;

                $fields = [
                    'producto'       => $val($data, $kProducto),
                    'descripcion'    => $val($data, $kDescripcion),
                    'categoria'      => $val($data, $kCategoria),
                    'marca_repuesto' => $val($data, $kMarcaRep),
                    'precio_venta'   => $val($data, $kPrecioVenta),
                    'precio_oferta'  => $val($data, $kPrecioOferta),
                    'stock_actual'   => $val($data, $kStock),
                    'condicion'      => $val($data, $kCondicion),
                    'garantia'       => $val($data, $kGarantia),
                    'origen'         => $val($data, $kOrigen),
                    'marca_compatible'  => $val($data, $kMarcaComp),
                    'modelo_compatible' => $val($data, $kModeloComp),
                    'cilindrada'        => $val($data, $kCilindrada),
                    'anios'             => $val($data, $kAnios),
                ];

                $skuBase[$sku] ??= array_fill_keys($baseFields, '');
                foreach ($baseFields as $f) {
                    if ($skuBase[$sku][$f] === '' && $fields[$f] !== '') {
                        $skuBase[$sku][$f] = $fields[$f];
                    }
                }

                $parsed[] = ['sku' => $sku, 'fields' => $fields];
            }

            // SEGUNDA PASADA: un producto por fila. Cada campo base se toma de la fila
            // si viene lleno; si no, del dato base del SKU.
            $rows = [];
            foreach ($parsed as $p) {
                $sku = $p['sku'];
                $f = $p['fields'];
                $base = $skuBase[$sku];

                $resolve = fn (string $field) => $f[$field] !== '' ? $f[$field] : $base[$field];

                $nombreFila = $resolve('producto');
                $descFila   = $resolve('descripcion');

                if ($hasDedicatedVehicleCols) {
                    $marcaCompatible  = $f['marca_compatible'];
                    $modeloCompatible = $f['modelo_compatible'];
                    $nombreVisible    = $nombreFila;
                    $nombreParaSlug   = $nombreFila;
                    $appendVehiculo   = true;
                } else {
                    $source = $nombreFila !== '' ? $nombreFila : $descFila;

                    if (str_contains($source, ',')) {
                        // Formato definitivo: "PRODUCTO, MARCA MODELO"
                        [$antesComa, $despuesComa] = explode(',', $source, 2);
                        $productoBase = trim($antesComa) !== '' ? trim($antesComa) : $source;
                        $bm = $matchBrandModel($cleanVehicleText($despuesComa));
                        $marcaCompatible  = $bm['marca'];
                        $modeloCompatible = $bm['modelo'];
                        $nombreVisible    = $nombreFila !== '' ? $nombreFila : $source; // se respeta tal cual viene en la planilla
                        $nombreParaSlug   = $productoBase;
                    } else {
                        // Formato antiguo de respaldo: "... COMPATIBLE CON X Y Z"
                        $ex = $extractVehicle($source);
                        $marcaCompatible  = $ex['marca'];
                        $modeloCompatible = $ex['modelo'];
                        $nombreVisible    = $nombreFila !== '' ? $nombreFila : $ex['nombre'];
                        $nombreParaSlug   = $ex['nombre'];
                    }
                    $appendVehiculo = false; // el nombre ya incluye el vehículo (con coma o con "COMPATIBLE CON")
                }

                $importKey = $sku . '|'
                    . \Illuminate\Support\Str::slug($marcaCompatible) . '|'
                    . \Illuminate\Support\Str::slug($modeloCompatible);

                $rows[$importKey] = [
                    'sku'            => $sku,
                    'import_key'     => $importKey,
                    'producto'       => $nombreVisible,
                    'producto_slug'  => $nombreParaSlug,
                    'append_vehiculo'=> $appendVehiculo,
                    'descripcion'    => $descFila,
                    'categoria'      => $resolve('categoria'),
                    'marca_repuesto' => $resolve('marca_repuesto'),
                    'precio_venta'   => $resolve('precio_venta'),
                    'precio_oferta'  => $resolve('precio_oferta'),
                    'stock_actual'   => $resolve('stock_actual'),
                    'condicion'      => $resolve('condicion'),
                    'garantia'       => $resolve('garantia'),
                    'origen'         => $resolve('origen'),
                    'marca_compatible'  => $marcaCompatible,
                    'modelo_compatible' => $modeloCompatible,
                    'cilindrada'        => $f['cilindrada'],
                    'anios'             => $f['anios'],
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
            $sinPrecio = 0;
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

                // 3. Precios y Stock. En CLP se acepta "$40.000", "40000", "40,000".
                $regularPrice = (int) preg_replace('/[^0-9]/', '', (string) $productData['precio_venta']);
                $wholesalePrice = (int) preg_replace('/[^0-9]/', '', (string) $productData['precio_oferta']);
                $stock = (int) preg_replace('/[^0-9]/', '', (string) $productData['stock_actual']);

                // Sin precio mayorista propio -> se usa el de venta (sin descuento).
                if ($wholesalePrice === 0 && $regularPrice > 0) {
                    $wholesalePrice = $regularPrice;
                }
                if ($regularPrice === 0) {
                    $sinPrecio++;
                }

                // 4. Vehículo compatible de ESTA fila (uno por producto)
                $marcaCompatible = $productData['marca_compatible'];
                $modeloCompatible = $productData['modelo_compatible'];
                $vehiculo = trim($marcaCompatible . ' ' . $modeloCompatible);

                // 5. Nombre visible. Si la planilla trae el vehículo en columnas
                //    aparte, se agrega al nombre para diferenciar las fichas del mismo
                //    SKU. Si el vehículo venía dentro del nombre ("... COMPATIBLE CON
                //    CHERY Y TIGGO 2"), se respeta el nombre tal cual.
                $baseName = trim($productData['producto']) ?: 'Sin nombre';
                $displayName = ($productData['append_vehiculo'] && $vehiculo !== '')
                    ? ($baseName . ' — ' . $vehiculo)
                    : $baseName;
                $slugSource = trim($productData['producto_slug'] ?? '') ?: $baseName;

                // 6. Descripción extendida
                $descExtra = [];
                if (!empty($productData['origen']))    $descExtra[] = "Origen: " . $productData['origen'];
                if (!empty($productData['condicion'])) $descExtra[] = "Condición: " . $productData['condicion'];
                if (!empty($productData['garantia']))  $descExtra[] = "Garantía: " . $productData['garantia'];
                if (!empty($productData['cilindrada'])) $descExtra[] = "Cilindrada: " . $productData['cilindrada'];
                // Sólo si el vehículo NO venía ya escrito en el nombre/descripción.
                if ($productData['append_vehiculo'] && $vehiculo !== '') $descExtra[] = "Compatible con: " . $vehiculo
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
                $slugBase = \Illuminate\Support\Str::slug($slugSource . '-' . $sku . ($modeloCompatible !== '' ? '-' . $modeloCompatible : ''));
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

            // La hora de descarga ayuda a diagnosticar el retraso de Google al publicar
            // (hasta 5 min): si acabas de editar hace menos de eso, esta sincronización
            // pudo traer todavía la versión anterior del archivo.
            $resumen = "Creados: {$created} · Actualizados: {$updated} · Total de filas: " . count($rows)
                . " · Descargado: {$fetchedAt}";

            if (! $kPrecioVenta) {
                Notification::make()
                    ->title('No se detectó ninguna columna de precio')
                    ->body('Encabezados detectados: ' . implode(', ', array_slice($header, 0, 25))
                        . '. Renombra la columna de precio a "Precio" o "Precio Venta". ' . $resumen)
                    ->warning()->persistent()->send();
            } elseif ($sinPrecio > 0) {
                Notification::make()
                    ->title("Sincronización completa — {$sinPrecio} producto(s) sin precio")
                    ->body("Esos productos quedaron en \$0 porque su fila (y las demás filas de su SKU) "
                        . "tenían la columna de precio vacía. {$resumen}")
                    ->warning()->persistent()->send();
            } else {
                Notification::make()
                    ->title('Sincronización completa')
                    ->body($resumen)
                    ->success()->send();
            }

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
