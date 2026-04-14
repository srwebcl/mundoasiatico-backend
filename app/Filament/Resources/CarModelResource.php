<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarModelResource\Pages;
use App\Models\Brand;
use App\Models\CarModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CarModelResource extends Resource
{
    protected static ?string $model = CarModel::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Modelo de Auto';
    protected static ?string $pluralModelLabel = 'Modelos de Auto';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Vehículo')
                ->schema([
                    Forms\Components\Select::make('brand_id')
                        ->label('Marca del Vehículo')
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del Modelo')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej: Tiggo 2 Pro')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                            $operation === 'create' ? $set('slug', Str::slug($state)) : null
                        ),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(CarModel::class, 'slug', ignoreRecord: true),

                    Forms\Components\TextInput::make('year_start')
                        ->label('Año Inicio')
                        ->numeric()
                        ->minValue(1990)
                        ->maxValue(2030)
                        ->placeholder('2019'),

                    Forms\Components\TextInput::make('year_end')
                        ->label('Año Fin (vacío = vigente)')
                        ->numeric()
                        ->minValue(1990)
                        ->maxValue(2030)
                        ->placeholder('2023'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Activo')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Marca')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Modelo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('year_start')
                    ->label('Desde'),

                Tables\Columns\TextColumn::make('year_end')
                    ->label('Hasta')
                    ->default('Vigente'),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Repuestos')
                    ->counts('products')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand_id')
                    ->label('Marca')
                    ->relationship('brand', 'name'),

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
            'index'  => Pages\ListCarModels::route('/'),
            'create' => Pages\CreateCarModel::route('/create'),
            'edit'   => Pages\EditCarModel::route('/{record}/edit'),
        ];
    }
}
