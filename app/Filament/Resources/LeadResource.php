<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Lead (Cotización WhatsApp)';
    protected static ?string $pluralModelLabel = 'Leads (Cotizaciones)';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'new')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos del Lead')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                            
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->required(),
                            
                        Forms\Components\TextInput::make('patente')
                            ->label('Patente del Vehículo'),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'new'       => 'Nuevo',
                                'contacted' => 'Contactado',
                                'converted' => 'Convertido a Venta',
                                'lost'      => 'Perdido',
                            ])
                            ->default('new')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Mensaje')
                    ->schema([
                        Forms\Components\Textarea::make('message')
                            ->label('Consulta Original')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),

                Tables\Columns\TextColumn::make('patente')
                    ->label('Patente')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Estado')
                    ->options([
                        'new'       => '🔴 Nuevo',
                        'contacted' => '🟡 Contactado',
                        'converted' => '🟢 Convertido',
                        'lost'      => '⚫ Perdido',
                    ]),

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
                        'new'       => 'Nuevo',
                        'contacted' => 'Contactado',
                        'converted' => 'Convertido a Venta',
                        'lost'      => 'Perdido',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
