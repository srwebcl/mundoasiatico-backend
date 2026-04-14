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

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Columna izquierda (2/3) ────────────────────────────────────────
            Forms\Components\Group::make([

                Forms\Components\Section::make('Información del Repuesto')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Repuesto')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            ),

                        Forms\Components\TextInput::make('sku')
                            ->label('SKU / Código')
                            ->required()
                            ->maxLength(100)
                            ->unique(Product::class, 'sku', ignoreRecord: true),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'slug', ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Precios y Stock')
                    ->schema([
                        Forms\Components\TextInput::make('regular_price')
                            ->label('Precio Retail (CLP)')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),

                        Forms\Components\TextInput::make('wholesale_price')
                            ->label('Precio Mayorista (CLP)')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->helperText('Generalmente entre 15% y 25% de descuento sobre el retail.'),

                        Forms\Components\TextInput::make('stock')
                            ->label('Stock Disponible')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])->columns(3),

                // ── Modelos de auto compatibles (KEY FEATURE) ─────────────────
                Forms\Components\Section::make('Compatibilidad de Vehículos')
                    ->description('Selecciona todos los modelos de auto en los que este repuesto es compatible.')
                    ->schema([
                        Forms\Components\Select::make('carModels')
                            ->label('Modelos Compatibles')
                            ->relationship('carModels', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull()
                            ->getOptionLabelFromRecordUsing(fn ($record) =>
                                "{$record->brand?->name} {$record->name}" .
                                ($record->year_start ? " ({$record->year_start}" . ($record->year_end ? "–{$record->year_end}" : "+") . ")" : "")
                            )
                            ->helperText('Puedes buscar escribiendo la marca o el modelo.'),
                    ]),

            ])->columnSpan(2),

            // ── Columna derecha (1/3) ──────────────────────────────────────────
            Forms\Components\Group::make([

                Forms\Components\Section::make('Imagen')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Foto del Repuesto')
                            ->image()
                            ->directory('products')
                            ->maxSize(3072)
                            ->imageEditor(),
                    ]),

                Forms\Components\Section::make('Clasificación')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('brand_id')
                            ->label('Marca del Repuesto')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Marca fabricante del repuesto (no del auto).'),
                    ]),

                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Publicado')
                            ->default(true),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Destacado en el Home')
                            ->default(false),
                    ]),

            ])->columnSpan(1),

        ])->columns(3);
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
                    ->label('Stock')
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state === 0   => 'danger',
                        $state <= 5    => 'warning',
                        default        => 'success',
                    })
                    ->sortable(),

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

                Tables\Filters\SelectFilter::make('brand_id')
                    ->label('Marca del Repuesto')
                    ->relationship('brand', 'name'),

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
