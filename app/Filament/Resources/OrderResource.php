<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Gestión';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Pedido';
    protected static ?string $pluralModelLabel = 'Pedidos';

    // ── Solo se puede cambiar el estado — el form es mínimo ──────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Actualizar Estado del Pedido')
                ->description('Los datos del pedido y sus montos son de solo lectura. Solo puedes cambiar el estado.')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options([
                            'pending'   => '⏳ Pendiente',
                            'paid'      => '✅ Pagado',
                            'failed'    => '❌ Fallido',
                            'shipped'   => '🚚 Enviado',
                            'cancelled' => '🚫 Cancelado',
                        ])
                        ->required(),
                ])->columns(1),
        ]);
    }

    // ── La vista de detalle usa Infolist (lectura) ────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            Infolists\Components\Section::make('Datos del Cliente')
                ->schema([
                    Infolists\Components\TextEntry::make('customer_name')->label('Nombre'),
                    Infolists\Components\TextEntry::make('customer_email')->label('Email')->copyable(),
                    Infolists\Components\TextEntry::make('customer_phone')->label('Teléfono'),
                    Infolists\Components\TextEntry::make('customer_rut')->label('RUT'),
                ])->columns(2),

            Infolists\Components\Section::make('Detalles del Pedido')
                ->schema([
                    Infolists\Components\TextEntry::make('id')
                        ->label('N° de Orden')
                        ->prefix('#'),

                    Infolists\Components\TextEntry::make('status')
                        ->label('Estado')
                        ->badge()
                        ->color(fn (string $state) => match ($state) {
                            'paid'      => 'success',
                            'pending'   => 'warning',
                            'shipped'   => 'info',
                            'failed'    => 'danger',
                            'cancelled' => 'gray',
                            default     => 'gray',
                        }),

                    Infolists\Components\TextEntry::make('total_amount')
                        ->label('Total Pagado')
                        ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.'))
                        ->weight('bold')
                        ->color('success'),

                    Infolists\Components\TextEntry::make('shipping_type')
                        ->label('Método de Envío')
                        ->formatStateUsing(fn (string $state) => match ($state) {
                            'retiro_stgo' => '🏢 Retiro en Santiago',
                            'retiro_pm'   => '🏢 Retiro en Puerto Montt',
                            'starken'     => '🚚 Despacho Starken',
                            default       => $state,
                        }),

                    Infolists\Components\TextEntry::make('shipping_address')
                        ->label('Dirección de Envío')
                        ->formatStateUsing(fn ($state) =>
                            is_array($state)
                                ? "{$state['street']} #{$state['number']}" . (isset($state['apto']) ? ", {$state['apto']}" : '') . ", {$state['city']}, {$state['region']}"
                                : '— Retiro en tienda —'
                        )
                        ->columnSpanFull(),

                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Fecha del Pedido')
                        ->dateTime('d/m/Y H:i'),
                ])->columns(2),

            Infolists\Components\Section::make('Transbank')
                ->schema([
                    Infolists\Components\TextEntry::make('transbank_token')
                        ->label('Token')
                        ->copyable()
                        ->fontFamily('mono'),

                    Infolists\Components\TextEntry::make('transbank_authorization_code')
                        ->label('Código de Autorización')
                        ->fontFamily('mono'),

                    Infolists\Components\TextEntry::make('transbank_transaction_date')
                        ->label('Fecha de Transacción'),
                ])->columns(3)->collapsible(),

            Infolists\Components\Section::make('Ítems del Pedido')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('items')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('product_sku')
                                ->label('SKU')
                                ->fontFamily('mono'),
                            Infolists\Components\TextEntry::make('product_name')
                                ->label('Producto'),
                            Infolists\Components\TextEntry::make('quantity')
                                ->label('Cant.'),
                            Infolists\Components\TextEntry::make('unit_price')
                                ->label('Precio Unit.')
                                ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.')),
                            Infolists\Components\TextEntry::make('subtotal')
                                ->label('Subtotal')
                                ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.'))
                                ->weight('bold'),
                        ])->columns(5),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->prefix('ORD-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'paid'      => 'success',
                        'pending'   => 'warning',
                        'shipped'   => 'info',
                        'failed'    => 'danger',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'paid'      => '✅ Pagado',
                        'pending'   => '⏳ Pendiente',
                        'shipped'   => '🚚 Enviado',
                        'failed'    => '❌ Fallido',
                        'cancelled' => '🚫 Cancelado',
                        default     => $state,
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('shipping_type')
                    ->label('Envío')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'retiro_stgo' => '🏢 Stgo',
                        'retiro_pm'   => '🏢 PM',
                        'starken'     => '🚚 Starken',
                        default       => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending'   => 'Pendiente',
                        'paid'      => 'Pagado',
                        'failed'    => 'Fallido',
                        'shipped'   => 'Enviado',
                        'cancelled' => 'Cancelado',
                    ]),
                Tables\Filters\SelectFilter::make('shipping_type')
                    ->label('Tipo de Envío')
                    ->options([
                        'retiro_stgo' => 'Retiro Santiago',
                        'retiro_pm'   => 'Retiro Puerto Montt',
                        'starken'     => 'Starken',
                    ]),
            ])
            ->actions([
                // Solo ver y editar el estado — sin delete
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->label('Cambiar Estado'),
            ])
            ->bulkActions([]); // Sin bulk actions en pedidos
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'view'   => Pages\ViewOrder::route('/{record}'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
