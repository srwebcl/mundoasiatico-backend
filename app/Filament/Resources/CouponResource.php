<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Cupón';
    protected static ?string $pluralModelLabel = 'Cupones';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Configuración del Cupón')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Código del Cupón')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->placeholder('EJ: BIENVENIDA10')
                        ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                        ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper($state) : null),

                    Forms\Components\Select::make('type')
                        ->label('Tipo de Descuento')
                        ->options([
                            'percent' => 'Porcentaje (%)',
                            'fixed'   => 'Monto Fijo (CLP)',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('value')
                        ->label('Valor del Descuento')
                        ->required()
                        ->numeric()
                        ->minValue(1),

                    Forms\Components\TextInput::make('min_amount')
                        ->label('Monto Mínimo de Compra')
                        ->numeric()
                        ->default(0)
                        ->prefix('$'),

                    Forms\Components\TextInput::make('max_uses')
                        ->label('Límite de Usos')
                        ->numeric()
                        ->helperText('Dejar en blanco para usos ilimitados.'),

                    Forms\Components\DateTimePicker::make('expires_at')
                        ->label('Fecha de Expiración')
                        ->nullable(),

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
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->fontFamily('mono')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => $state === 'percent' ? 'Porcentaje' : 'Fijo'),

                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percent' ? $state . '%' : '$' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('used_count')
                    ->label('Usos')
                    ->suffix(fn ($record) => $record->max_uses ? " / {$record->max_uses}" : ''),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expira')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Sin límite'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Solo Activos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
