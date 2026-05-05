<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource;
use App\Models\Product;
use App\Services\InventoryUnitConverter;
use Filament\Facades\Filament;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInventory extends CreateRecord
{
    protected static string $resource = InventoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $product = Product::find($data['product_id'] ?? null);

        $data['team_id'] = $data['team_id'] ?? Filament::getTenant()?->id;
        $data['product_name'] = $data['product_name'] ?? $product?->name;
        $data['quantity'] = app(InventoryUnitConverter::class)
            ->commercialToStorageUnits((float) ($data['quantity'] ?? 0), $product);

        return $data;
    }
}
