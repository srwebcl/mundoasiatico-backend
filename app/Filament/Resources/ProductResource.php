<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Repuesto';
    protected static ?string $pluralModelLabel = 'Repuestos';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'SKU' => $record->sku,
            'Precio' => '$' . number_format($record->regular_price, 0, ',', '.'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Product Tabs')
                ->tabs([
                    // ── PESTAÑA: INFORMACIÓN GENERAL ──────────────────────────────
                    Forms\Components\Tabs\Tab::make('General')
                        ->icon('heroicon-m-information-circle')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nombre del Repuesto')
                                        ->placeholder('Ej: Filtro de Aceite Chery Tiggo 2')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(2)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                                            $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                        ),

                                    Forms\Components\TextInput::make('sku')
                                        ->label('SKU / Código')
                                        ->placeholder('ABC-123')
                                        ->required()
                                        ->maxLength(100)
                                        ->unique(Product::class, 'sku', ignoreRecord: true)
                                        ->columnSpan(1),
                                ]),

                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('category_id')
                                        ->label('Categoría')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->columnSpan(2),
                                ]),

                            Forms\Components\RichEditor::make('description')
                                ->label('Descripción Detallada')
                                ->placeholder('Describe las especificaciones técnicas del repuesto...')
                                ->columnSpanFull()
                                ->toolbarButtons([
                                    'bold', 'italic', 'bulletList', 'orderedList', 'link', 'redo', 'undo',
                                ]),
                            
                            Forms\Components\TextInput::make('slug')
                                ->label('Slug (URL amigable)')
                                ->required()
                                ->maxLength(255)
                                ->unique(Product::class, 'slug', ignoreRecord: true)
                                ->helperText('Se genera automáticamente del nombre.'),
                        ]),

                    // ── PESTAÑA: PRECIOS E INVENTARIO ────────────────────────────
                    Forms\Components\Tabs\Tab::make('Precios y Stock')
                        ->icon('heroicon-m-currency-dollar')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Section::make('Precios')
                                        ->description('Define los valores para clientes retail y mayoristas.')
                                        ->schema([
                                            Forms\Components\TextInput::make('regular_price')
                                                ->label('Precio Público (CLP)')
                                                ->required()
                                                ->numeric()
                                                ->prefix('$'),

                                            Forms\Components\TextInput::make('wholesale_price')
                                                ->label('Precio Mayorista (CLP)')
                                                ->required()
                                                ->numeric()
                                                ->prefix('$'),
                                        ])->columnSpan(1),

                                    Forms\Components\Section::make('Inventario')
                                        ->description('Control de existencias y reservas.')
                                        ->schema([
                                            Forms\Components\TextInput::make('stock')
                                                ->label('Stock Actual en Bodega')
                                                ->required()
                                                ->numeric()
                                                ->minValue(0)
                                                ->default(0),

                                            Forms\Components\TextInput::make('stock_reserved')
                                                ->label('Reserva Activa')
                                                ->numeric()
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->helperText('Unidades bloqueadas para pagos en curso.'),
                                        ])->columnSpan(1),
                                ]),
                        ]),

                    // ── PESTAÑA: COMPATIBILIDAD ──────────────────────────────────
                    Forms\Components\Tabs\Tab::make('Compatibilidad')
                        ->icon('heroicon-m-key')
                        ->schema([
                            Forms\Components\Select::make('carModels')
                                ->label('Vincular a Modelos de Autos')
                                ->helperText('Busca y selecciona los modelos de auto (Ej: Chery Tiggo 2).')
                                ->relationship(
                                    name: 'carModels', 
                                    modifyQueryUsing: fn ($query) => $query
                                        ->leftJoin('brands', 'car_models.brand_id', '=', 'brands.id')
                                        ->select('car_models.*')
                                        ->with('brand')
                                )
                                ->multiple()
                                ->searchable(['car_models.name', 'brands.name'])

                                ->getOptionLabelFromRecordUsing(fn ($record) =>
                                    "{$record->brand?->name} {$record->name} " .
                                    ($record->year_start ? "({$record->year_start}-" . ($record->year_end ?: 'Hoy') . ")" : "")
                                )
                                ->columnSpanFull(),
                        ]),

                    // ── PESTAÑA: MEDIA Y VISIBILIDAD ─────────────────────────────
                    Forms\Components\Tabs\Tab::make('Media y Estado')
                        ->icon('heroicon-m-photo')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\Group::make([
                                        Forms\Components\FileUpload::make('image')
                                            ->label('Fotografía Principal')
                                            ->image()
                                            ->directory('products')
                                            ->imageEditor(),
                                            
                                        Forms\Components\FileUpload::make('gallery')
                                            ->label('Galería Adicional (Multiples Fotos)')
                                            ->helperText('Añade más fotos. Puedes arrastrarlas para reordenarlas.')
                                            ->image()
                                            ->multiple()
                                            ->reorderable()
                                            ->appendFiles()
                                            ->directory('products')
                                            ->imageEditor(),
                                    ])->columnSpan(2),

                                    Forms\Components\Section::make('Visibilidad')
                                        ->schema([
                                            Forms\Components\Toggle::make('is_active')
                                                ->label('Producto Visible en la Tienda')
                                                ->helperText('Si se desactiva, el producto no aparecerá en el catálogo.')
                                                ->default(true),

                                            Forms\Components\Toggle::make('is_featured')
                                                ->label('Destacar en Portada')
                                                ->helperText('Aparecerá en la sección de recomendados del inicio.')
                                                ->default(false),
                                        ])->columnSpan(1),
                                ]),
                        ]),
                        
                    // ── PESTAÑA: SEO ──────────────────────────────────────────────────
                    Forms\Components\Tabs\Tab::make('SEO')
                        ->icon('heroicon-m-globe-alt')
                        ->schema([
                            Forms\Components\TextInput::make('meta_title')
                                ->label('Meta Título (SEO)')
                                ->placeholder('Ej: Comprar Filtro de Aceite Chery | Mundo Asiático')
                                ->maxLength(60)
                                ->helperText('Recomendado: Máximo 60 caracteres. Déjalo en blanco para usar el nombre del producto.'),
                                
                            Forms\Components\Textarea::make('meta_description')
                                ->label('Meta Descripción (SEO)')
                                ->placeholder('Ej: Encuentra el mejor filtro de aceite para Chery Tiggo 2. Despacho a todo Chile y garantía de 3 meses.')
                                ->maxLength(160)
                                ->helperText('Aparecerá bajo el enlace en los resultados de Google. Máximo 160 caracteres.'),
                        ]),
                ])
                ->activeTab(1)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->square()
                    ->size(48),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('regular_price')
                    ->label('Precio Retail')
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('wholesale_price')
                    ->label('Precio Mayorista')
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock Total')
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state === 0   => 'danger',
                        $state <= 5    => 'warning',
                        default        => 'success',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_reserved')
                    ->label('Reservado')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Pub.')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Dest.')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Publicados'),

                Tables\Filters\Filter::make('sin_stock')
                    ->label('Sin Stock')
                    ->query(fn ($query) => $query->where('stock', 0)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
