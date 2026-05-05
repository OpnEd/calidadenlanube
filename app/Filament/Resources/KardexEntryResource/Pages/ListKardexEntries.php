<?php

namespace App\Filament\Resources\KardexEntryResource\Pages;

use App\Filament\Resources\KardexEntryResource;
use App\Models\KardexEntry;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListKardexEntries extends ListRecords
{
    protected static string $resource = KardexEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Todos'),
        ];

        $productNames = KardexEntry::query()
            ->select('inventories.product_name')
            ->join('inventories', 'kardex_entries.inventory_id', '=', 'inventories.id')
            ->whereNotNull('inventories.product_name')
            ->distinct()
            ->orderBy('inventories.product_name')
            ->pluck('inventories.product_name');

        foreach ($productNames as $productName) {
            $tabs['product_' . md5($productName)] = Tab::make($productName)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereHas(
                    'inventory',
                    fn (Builder $inventoryQuery): Builder => $inventoryQuery->where('product_name', $productName)
                ));
        }

        return $tabs;
    }
}
