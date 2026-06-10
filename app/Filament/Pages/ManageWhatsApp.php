<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\Setting;

class ManageWhatsApp extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationLabel = 'WhatsApp';
    protected static ?string $title = 'Número de WhatsApp';
    protected static ?string $slug = 'whatsapp';

    protected static string $view = 'filament.pages.manage-whatsapp';

    public ?array $data = [];

    public function mount(): void
    {
        $whatsapp = Setting::firstOrCreate(
            ['key' => 'whatsapp_number'],
            ['label' => 'Número de WhatsApp Front', 'value' => '56941737497', 'type' => 'text']
        );
        $this->form->fill([
            'number' => $whatsapp->value,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('number')
                    ->label('Número de WhatsApp')
                    ->helperText('Incluye el código de país. Ejemplo: 56912345678')
                    ->required()
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $whatsapp = Setting::where('key', 'whatsapp_number')->first();
        if ($whatsapp) {
            $whatsapp->update(['value' => $this->data['number']]);
        }

        Notification::make()
            ->title('Guardado correctamente')
            ->success()
            ->send();
    }
}
