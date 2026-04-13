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
            //
            Stat::make('total_orders', Order::where('status', 'new')->count())
                ->description('new order')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->label('New Orders')
                ->chart([7, 2, 10, 3, 15, 4, 20, 5, 25, 6])
                ->color('primary')
                ->description('new orders placed.'),

            Stat::make('total_orders', Order::where('status', 'processing')->count())
                ->description('Processing Order')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->label('Processing Orders')
                ->chart([7, 2, 10, 3, 15, 4, 20, 5, 25, 6])
                ->color('warning')
                ->description('Processing orders.'),
            Stat::make('total_orders', Order::where('status', 'completed')->count())
                ->description('Completed Order')
                ->descriptionIcon('heroicon-o-check-badge')
                ->label('Completed Orders')
                ->chart([7, 2, 10, 3, 15, 4, 20, 5, 25, 6])
                ->color('success')
                ->description('Completed orders.'),
            Stat::make('total_orders', 'Rp. '.number_format(Order::where('status', 'completed')->sum('total_payment'), 0, '.', ','))
                ->description('Completed Order')
                ->descriptionIcon('heroicon-o-banknotes')
                ->label('Completed Orders')
                ->chart([7, 2, 10, 3, 15, 4, 20, 5, 25, 6])
                ->color('success')
                ->description('Completed orders.'),

        ];
    }
}
