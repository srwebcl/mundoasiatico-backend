<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Órdenes Pendientes', Order::where('status', 'pending')->count())
                ->description('Por procesar y despachar')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Ventas Hoy', '$' . number_format(Order::where('status', 'paid')->whereDate('created_at', today())->sum('total_amount'), 0, ',', '.'))
                ->description('Total facturado hoy')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Ticket Promedio', '$' . number_format(Order::where('status', 'paid')->avg('total_amount') ?? 0, 0, ',', '.'))
                ->description('Valor medio por compra')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }
}
