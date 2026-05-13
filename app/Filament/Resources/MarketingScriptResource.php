<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingScriptResource\Pages;
use App\Models\MarketingScript;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MarketingScriptResource extends Resource
{
    protected static ?string $model = MarketingScript::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Script';
    protected static ?string $pluralModelLabel = 'Scripts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración del Script')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre identificador')
                            ->placeholder('Ej: Google Analytics 4')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\Select::make('type')
                            ->label('Tipo de Script')
                            ->options([
                                'analytics' => 'Google Analytics',
                                'gtm'       => 'Google Tag Manager',
                                'meta'      => 'Meta Pixel (Facebook)',
                                'custom'    => 'Personalizado',
                            ])
                            ->default('custom')
                            ->required(),

                        Forms\Components\Select::make('placement')
                            ->label('Ubicación de Inyección')
                            ->options([
                                'head' => 'Dentro del <head> (Recomendado para GTM/Analytics)',
                                'body' => 'Al inicio del <body>',
                            ])
                            ->default('head')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Script Activo')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Código del Script')
                    ->schema([
                        Forms\Components\Textarea::make('code')
                            ->label('Código a inyectar (Snippet HTML/JS completo o ID según corresponda)')
                            ->required()
                            ->columnSpanFull()
                            ->rows(8)
                            ->extraAttributes(['style' => 'font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.875rem;'])
                            ->placeholder('<script>...</script>'),
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
                    
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'analytics' => 'info',
                        'gtm'       => 'warning',
                        'meta'      => 'primary',
                        default     => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('placement')
                    ->label('Ubicación')
                    ->formatStateUsing(fn ($state) => strtoupper($state)),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Activo'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Filtrar por Tipo')
                    ->options([
                        'analytics' => 'Google Analytics',
                        'gtm'       => 'Google Tag Manager',
                        'meta'      => 'Meta Pixel',
                        'custom'    => 'Personalizado',
                    ]),
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
            'index' => Pages\ListMarketingScripts::route('/'),
            'create' => Pages\CreateMarketingScript::route('/create'),
            'edit' => Pages\EditMarketingScript::route('/{record}/edit'),
        ];
    }
}
