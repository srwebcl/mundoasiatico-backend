<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PopupResource\Pages;
use App\Models\Popup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PopupResource extends Resource
{
    protected static ?string $model = Popup::class;

    protected static ?string $navigationIcon = 'heroicon-o-window';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Pop-up';
    protected static ?string $pluralModelLabel = 'Pop-up';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración General')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título Interno')
                            ->placeholder('Ej: Cyber Day - 20% Off')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('image')
                            ->label('Imagen del Pop-up')
                            ->image()
                            ->directory('popups')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('content')
                            ->label('Texto Promocional')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold', 'italic', 'link', 'h2', 'h3'
                            ]),

                    ])->columns(1),

                Forms\Components\Section::make('Botón a la Acción (Call to Action)')
                    ->schema([
                        Forms\Components\TextInput::make('button_text')
                            ->label('Texto del Botón')
                            ->placeholder('Ej: ¡Lo quiero!'),

                        Forms\Components\TextInput::make('button_link')
                            ->label('URL de Destino')
                            ->url()
                            ->placeholder('https://...'),
                    ])->columns(2),

                Forms\Components\Section::make('Comportamiento')
                    ->schema([
                        Forms\Components\TextInput::make('delay_seconds')
                            ->label('Retraso antes de mostrar (segundos)')
                            ->numeric()
                            ->default(3)
                            ->required(),

                        Forms\Components\TextInput::make('target_url')
                            ->label('Mostrar solo en ruta (Opcional)')
                            ->placeholder('Ej: /catalogo. Dejar vacío para todas las páginas.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Pop-up Activado')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagen'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('delay_seconds')
                    ->label('Retraso')
                    ->formatStateUsing(fn ($state) => $state . ' segs'),

                Tables\Columns\TextColumn::make('target_url')
                    ->label('Ruta Específica')
                    ->default('Todas las páginas'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Activo'),
            ])
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPopups::route('/'),
            'create' => Pages\CreatePopup::route('/create'),
            'edit' => Pages\EditPopup::route('/{record}/edit'),
        ];
    }
}
