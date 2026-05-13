<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Cliente (CRM)';
    protected static ?string $pluralModelLabel = 'Clientes (CRM)';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'rut', 'phone'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->name . ' (' . $record->email . ')';
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->whereIn('role', ['customer', 'wholesale']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos Personales')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre Completo')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(User::class, 'email', ignoreRecord: true),

                    Forms\Components\TextInput::make('phone')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(20),

                    Forms\Components\TextInput::make('rut')
                        ->label('RUT')
                        ->maxLength(12)
                        ->unique(User::class, 'rut', ignoreRecord: true),

                    Forms\Components\TextInput::make('patente')
                        ->label('Patente Vehículo')
                        ->maxLength(20)
                        ->placeholder('Ej: ABCD12'),

                    Forms\Components\Select::make('role')
                        ->label('Rol del Usuario')
                        ->options([
                            'customer'  => '🛒 Cliente Retail',
                            'wholesale' => '🏭 Cliente Mayorista',
                            'admin'     => '🔑 Administrador',
                        ])
                        ->required()
                        ->default('customer'),

                    Forms\Components\TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->minLength(8)
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation) => $operation === 'create')
                        ->helperText('Dejar en blanco para no cambiar la contraseña.'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('rut')
                    ->label('RUT')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('patente')
                    ->label('Patente')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'admin'     => 'danger',
                        'wholesale' => 'warning',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'admin'     => '🔑 Admin',
                        'wholesale' => '🏭 Mayorista',
                        default     => '🛒 Cliente',
                    }),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('N° Compras')
                    ->counts('orders')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total Gastado')
                    ->getStateUsing(fn (\App\Models\User $record) => '$' . number_format($record->orders()->where('status', 'paid')->sum('total_amount'), 0, ',', '.'))
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        'customer'  => 'Cliente Retail',
                        'wholesale' => 'Mayorista',
                        'admin'     => 'Administrador',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
