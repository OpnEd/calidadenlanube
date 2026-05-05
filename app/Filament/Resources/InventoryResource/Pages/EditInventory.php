<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource;
use App\Models\Product;
use App\Services\InventoryUnitConverter;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventory extends EditRecord
{
    protected static string $resource = InventoryResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $product = Product::find($data['product_id'] ?? null);

        $data['quantity'] = app(InventoryUnitConverter::class)
            ->storageToCommercialUnits((float) ($data['quantity'] ?? 0), $product);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $product = Product::find($data['product_id'] ?? null);

        $data['product_name'] = $data['product_name'] ?? $product?->name;
        $data['quantity'] = app(InventoryUnitConverter::class)
            ->commercialToStorageUnits((float) ($data['quantity'] ?? 0), $product);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
