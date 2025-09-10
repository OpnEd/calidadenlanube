<?php

namespace App\Filament\TenantManager\Resources\ProductReceptionItemResource\Pages;

use App\Filament\TenantManager\Resources\ProductReceptionItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductReceptionItems extends ListRecords
{
    protected static string $resource = ProductReceptionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
