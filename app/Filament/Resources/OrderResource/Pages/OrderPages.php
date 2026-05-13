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

    protected function getHeaderWidgets(): array
    {
        return [
            OrderResource\Widgets\OrderStats::class,
        ];
    }
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

    protected function afterSave(): void
    {
        $order = $this->record;

        // Notificación de Despachado
        if ($order->status === \App\Models\Order::STATUS_SHIPPED) {
            try {
                \Illuminate\Support\Facades\Mail::to($order->customer_email)->send(new \App\Mail\OrderShippedMail($order));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("No se pudo enviar OrderShippedMail: " . $e->getMessage());
            }
        }

        // Notificación de Entregado
        if ($order->status === \App\Models\Order::STATUS_DELIVERED) {
            try {
                \Illuminate\Support\Facades\Mail::to($order->customer_email)->send(new \App\Mail\OrderDeliveredMail($order));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("No se pudo enviar OrderDeliveredMail: " . $e->getMessage());
            }
        }
    }
}
