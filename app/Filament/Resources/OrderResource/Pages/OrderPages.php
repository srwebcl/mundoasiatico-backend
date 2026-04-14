<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Pages\EditRecord;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
    // Sin botón de crear — los pedidos los crea el proceso de checkout
    protected function getHeaderActions(): array { return []; }
}

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Cambiar Estado'),
        ];
    }
}

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        // Sin botón de eliminar en pedidos
        return [];
    }
}
