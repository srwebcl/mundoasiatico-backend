<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class PromoBarSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 5;
    protected static ?string $title = 'Banner Superior';
    protected static ?string $navigationLabel = 'Banner Superior';

    protected static string $view = 'filament.pages.promo-bar-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::where('key', 'like', 'promo_bar_%')
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        $this->form->fill([
            'enabled'     => ($settings['promo_bar_enabled'] ?? '0') === '1',
            'text'        => $settings['promo_bar_text'] ?? '',
            'url'         => $settings['promo_bar_url'] ?? '',
            'color'       => $settings['promo_bar_color'] ?? '#0a0a0a',
            'text_color'  => $settings['promo_bar_text_color'] ?? '#ffffff',
            'font_weight' => $settings['promo_bar_font_weight'] ?? 'bold',
            'icon'        => $settings['promo_bar_icon'] ?? 'heroicon-o-truck',
            'animate'     => ($settings['promo_bar_animate'] ?? '0') === '1',
            'start_at'    => $settings['promo_bar_start_at'] ?? null,
            'end_at'      => $settings['promo_bar_end_at'] ?? null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make('Contenido y Enlace')
                            ->columnSpan(2)
                            ->schema([
                                Toggle::make('enabled')
                                    ->label('Banner Visible')
                                    ->default(true),

                                TextInput::make('text')
                                    ->label('Mensaje Promocional')
                                    ->required()
                                    ->placeholder('Ej: Despacho Gratis sobre $100.000'),

                                TextInput::make('url')
                                    ->label('Enlace (URL)')
                                    ->placeholder('https://mundoasiatico.cl/ofertas')
                                    ->helperText('Opcional. Si se llena, el banner será clickeable.'),

                                Select::make('icon')
                                    ->label('Icono Destacado')
                                    ->options([
                                        'heroicon-o-truck'           => 'Camión (Despacho)',
                                        'heroicon-o-fire'            => 'Fuego (Oferta)',
                                        'heroicon-o-gift'            => 'Regalo',
                                        'heroicon-o-sparkles'        => 'Chispas (Nuevo)',
                                        'heroicon-o-megaphone'       => 'Megáfono',
                                        'heroicon-o-information-circle' => 'Información',
                                    ])
                                    ->default('heroicon-o-truck'),
                            ]),

                        Section::make('Diseño Visual')
                            ->columnSpan(1)
                            ->schema([
                                ColorPicker::make('color')
                                    ->label('Fondo (Background)'),

                                ColorPicker::make('text_color')
                                    ->label('Color del Texto'),

                                Select::make('font_weight')
                                    ->label('Estilo de Fuente')
                                    ->options([
                                        'normal' => 'Normal',
                                        'medium' => 'Media',
                                        'bold'   => 'Negrita (Bold)',
                                        'black'  => 'Extra Negrita',
                                    ]),

                                Toggle::make('animate')
                                    ->label('Efecto Animado (Pulso)')
                                    ->helperText('Añade un parpadeo suave para llamar la atención.'),
                            ]),

                        Section::make('Programación Automática')
                            ->columnSpanFull()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        DateTimePicker::make('start_at')
                                            ->label('Fecha/Hora Inicio')
                                            ->helperText('Dejar vacío para activar de inmediato.'),

                                        DateTimePicker::make('end_at')
                                            ->label('Fecha/Hora Fin')
                                            ->helperText('Dejar vacío para que sea permanente.'),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Publicar Cambios')
                ->color('danger')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            $dbKey = "promo_bar_{$key}";
            $finalValue = is_bool($value) ? ($value ? '1' : '0') : $value;
            Setting::where('key', $dbKey)->update(['value' => $finalValue]);
        }

        Notification::make()
            ->title('¡Banner actualizado y publicado!')
            ->success()
            ->send();
    }
}
