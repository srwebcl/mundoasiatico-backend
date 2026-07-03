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
            $lines = explode("\n", $csv);
            
            if (count($lines) < 2) {
                Notification::make()->title('El archivo parece estar vacío.')->warning()->send();
                return;
            }

            $header = str_getcsv(array_shift($lines));
            
            $count = 0;
            foreach ($lines as $line) {
                if (trim($line) === '') continue;
                $row = str_getcsv($line);
                if (count($row) < count($header)) continue;
                
                $data = array_combine($header, $row);
                
                $normalized = [];
                foreach ($data as $k => $v) {
                    // Normalize keys: lower, trim, space to underscore
                    $key = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', trim($k)));
                    $normalized[$key] = trim($v);
                }
                
                if (empty($normalized['sku'])) continue;
                
                $brandId = null;
                if (!empty($normalized['marca'])) {
                    $brand = Brand::firstOrCreate(
                        ['name' => $normalized['marca']],
                        ['slug' => \Illuminate\Support\Str::slug($normalized['marca']), 'is_active' => true]
                    );
                    $brandId = $brand->id;
                }
                
                $catId = null;
                if (!empty($normalized['categoria'])) {
                    $cat = Category::firstOrCreate(
                        ['name' => $normalized['categoria']],
                        ['slug' => \Illuminate\Support\Str::slug($normalized['categoria']), 'is_active' => true]
                    );
                    $catId = $cat->id;
                }
                
                // Get price handling both "precio" and "precio_normal"
                $priceStr = $normalized['precio_normal'] ?? $normalized['precio'] ?? '0';
                $regularPrice = (int) preg_replace('/[^0-9]/', '', $priceStr);
                
                $wholesalePriceStr = $normalized['precio_mayorista'] ?? '0';
                $wholesalePrice = (int) preg_replace('/[^0-9]/', '', $wholesalePriceStr);

                $stockStr = $normalized['stock'] ?? '0';
                $stock = (int) preg_replace('/[^0-9]/', '', $stockStr);

                Product::updateOrCreate(
                    ['sku' => $normalized['sku']],
                    [
                        'name' => $normalized['nombre'] ?? 'Sin nombre',
                        'slug' => \Illuminate\Support\Str::slug($normalized['nombre'] ?? $normalized['sku']),
                        'description' => $normalized['descripcion'] ?? '',
                        'regular_price' => $regularPrice,
                        'wholesale_price' => $wholesalePrice,
                        'stock' => $stock,
                        'brand_id' => $brandId,
                        'category_id' => $catId,
                    ]
                );
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
                    ->helperText('¡IMPORTANTE! Cada imagen debe llamarse EXACTAMENTE igual que su SKU (ej. FILTRO-001.jpg)')
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
        
        try {
            $manager = new ImageManager(new Driver());
        } catch (\Exception $e) {
            Notification::make()->title('Falta configurar driver GD o librería Intervention Image.')->danger()->send();
            return;
        }

        $processed = 0;
        $notFound = 0;
        
        foreach ($data['images'] as $tempPath) {
            $absolutePath = storage_path('app/public/' . $tempPath);
            if (!file_exists($absolutePath)) continue;
            
            $filename = basename($tempPath);
            $sku = pathinfo($filename, PATHINFO_FILENAME);
            
            $product = Product::where('sku', $sku)->first();
            if (!$product) {
                $notFound++;
                unlink($absolutePath); // Limpiar igual
                continue;
            }
            
            try {
                $image = $manager->read($absolutePath);
                
                // Escalar a máximo 1200px de ancho si es muy grande
                if ($image->width() > 1200) {
                    $image->scale(width: 1200);
                }
                
                // Generar ruta y convertir a WebP (calidad 80)
                $newFilename = 'products/' . $sku . '-' . uniqid() . '.webp';
                $destPath = storage_path('app/public/' . $newFilename);
                
                if (!file_exists(dirname($destPath))) {
                    mkdir(dirname($destPath), 0755, true);
                }
                
                $image->toWebp(80)->save($destPath);
                
                // Actualizar producto en BD
                $product->update(['image' => $newFilename]);
                $processed++;
                
                // Eliminar el archivo original temporal pesado
                unlink($absolutePath);
            } catch (\Exception $e) {
                // Si falla procesar una foto, continuar con las demás
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
