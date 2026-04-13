<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

use function Symfony\Component\Translation\t;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return
        [
            \App\Filament\Resources\OrderResource\Widgets\OrderStats::class,
        ];
    }
    public function getTabs(): array
    {
        return[
            null => Tab::make('All'),
            'new' => Tab::make()->query(fn($query) => $query->where('status', 'new'))->label('New'),
            'processing' => Tab::make()->query(fn($query) => $query->where('status', 'processing'))->label('Processing'),
            'completed' => Tab::make()->query(fn($query) => $query->where('status', 'completed'))->label('Completed'),
            'cancelled' => Tab::make()->query(fn($query) => $query->where('status', 'cancelled'))->label('Cancelled'),
        ];
    }
}
