<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Banner Principal';
    protected static ?string $modelLabel = 'Banner Principal';
    protected static ?string $pluralModelLabel = 'Banners Principales';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Imagen del Banner')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Imagen (Recomendado 1920x1080)')
                            ->image()
                            ->directory('hero-slides')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Contenido de Texto')
                    ->schema([
                        Forms\Components\TextInput::make('subtitle')
                            ->label('Subtítulo (ej. SEGURIDAD TOTAL)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('title')
                            ->label('Título Principal')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Botón de Acción (Call to Action)')
                    ->schema([
                        Forms\Components\TextInput::make('cta_text')
                            ->label('Texto del Botón (ej. VER FRENOS)')
                            ->maxLength(255),
                        Forms\Components\Select::make('cta_link')
                            ->label('Enlace del Botón')
                            ->options(function () {
                                $options = [
                                    'Páginas Generales' => [
                                        '/catalogo' => 'Catálogo Completo',
                                    ],
                                ];
                                
                                $categories = \App\Models\Category::where('is_active', true)
                                    ->pluck('name', 'slug')
                                    ->mapWithKeys(fn($name, $slug) => ["/catalogo?categoria={$slug}" => "Categoría: {$name}"])
                                    ->toArray();
                                
                                if (!empty($categories)) {
                                    $options['Categorías'] = $categories;
                                }
                                
                                $brands = \App\Models\Brand::where('is_active', true)
                                    ->pluck('name', 'slug')
                                    ->mapWithKeys(fn($name, $slug) => ["/catalogo?marca={$slug}" => "Marca: {$name}"])
                                    ->toArray();
                                    
                                if (!empty($brands)) {
                                    $options['Marcas'] = $brands;
                                }
                                
                                return $options;
                            })
                            ->searchable()
                            ->native(false)
                            ->columnSpan(1),
                    ])->columns(2),

                Forms\Components\Section::make('Configuración')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                        Forms\Components\TextInput::make('order')
                            ->label('Orden de aparición')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Imagen'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cta_text')
                    ->label('Botón')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeroSlides::route('/'),
            'create' => Pages\CreateHeroSlide::route('/create'),
            'edit' => Pages\EditHeroSlide::route('/{record}/edit'),
        ];
    }
}
