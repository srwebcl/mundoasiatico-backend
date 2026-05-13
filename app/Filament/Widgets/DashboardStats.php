<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // 1. Ingresos Mensuales
        $currentMonthRevenue = Order::where('status', Order::STATUS_PAID)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('total_amount');
            
        $previousMonthRevenue = Order::where('status', Order::STATUS_PAID)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('total_amount');

        $revenueTrend = $currentMonthRevenue >= $previousMonthRevenue ? 'success' : 'danger';
        $revenueIcon = $currentMonthRevenue >= $previousMonthRevenue ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        
        // 2. Órdenes Pendientes
        $pendingOrders = Order::where('status', Order::STATUS_PENDING)->count();

        // 3. Nuevos Clientes (mes actual)
        $newCustomers = User::where('role', 'customer')
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        return [
            Stat::make('Ingresos del Mes', '$' . number_format($currentMonthRevenue, 0, ',', '.'))
                ->description('Vs mes anterior ($' . number_format($previousMonthRevenue, 0, ',', '.') . ')')
                ->descriptionIcon($revenueIcon)
                ->color($revenueTrend)
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Chart visual fake para estética

            Stat::make('Órdenes por Procesar', $pendingOrders)
                ->description('Órdenes en estado pendiente')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),

            Stat::make('Nuevos Clientes', $newCustomers)
                ->description('Clientes registrados este mes')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([1, 4, 2, 8, 5, 9, 12]),
        ];
    }
}
