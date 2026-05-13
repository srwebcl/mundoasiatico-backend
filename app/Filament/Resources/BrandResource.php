<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Marca';
    protected static ?string $pluralModelLabel = 'Marcas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información de la Marca')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                            $operation === 'create' ? $set('slug', Str::slug($state)) : null
                        ),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug (URL)')
                        ->required()
                        ->maxLength(255)
                        ->unique(Brand::class, 'slug', ignoreRecord: true),

                    Forms\Components\FileUpload::make('logo')
                        ->label('Logo de la Marca')
                        ->image()
                        ->directory('brands')
                        ->maxSize(1024)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Activa')
                        ->default(true),
                ])->columns(2),

            Forms\Components\Section::make('Modelos de Auto')
                ->description('Administra los modelos de vehículos que pertenecen a esta marca. (Ej: Tiggo 2, H6, etc.)')
                ->schema([
                    Forms\Components\Repeater::make('carModels')
                        ->relationship('carModels')
                        ->label('')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nombre del Modelo')
                                ->placeholder('Ej: Tiggo 2')
                                ->required()
                                ->maxLength(255),
                            
                            Forms\Components\TextInput::make('year_start')
                                ->label('Año Inicio')
                                ->numeric()
                                ->placeholder('Ej: 2018'),
                                
                            Forms\Components\TextInput::make('year_end')
                                ->label('Año Fin')
                                ->numeric()
                                ->placeholder('Vacío si es actual'),
                        ])
                        ->columns(3)
                        ->columnSpanFull()
                        ->addActionLabel('Añadir Otro Modelo')
                        ->defaultItems(0)
                        ->collapsible(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->square()
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label('Marca')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->copyable(),

                Tables\Columns\TextColumn::make('car_models_count')
                    ->label('Modelos de Auto')
                    ->counts('carModels')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Estado'),
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit'   => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
