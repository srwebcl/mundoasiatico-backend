<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Órdenes de hoy
        $ordersToday = Order::whereDate('created_at', Carbon::today())->count();
        
        // Ventas Totales Históricas (Solo pagadas o completadas)
        $totalSales = Order::whereIn('status', [Order::STATUS_PAID, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED])->sum('total_amount');
        
        // Repuestos sin stock o crítico (<= 5)
        $criticalStock = Product::where('stock', '<=', 5)->where('is_active', true)->count();

        return [
            Stat::make('Órdenes (Hoy)', $ordersToday)
                ->description('Nuevos pedidos ingresados hoy')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Ingresos Totales', '$ ' . number_format($totalSales, 0, ',', '.'))
                ->description('Ventas confirmadas e históricas')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Alertas de Stock', $criticalStock)
                ->description('Repuestos con 5 o menos unidades')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($criticalStock > 0 ? 'danger' : 'success'),
        ];
    }
}
