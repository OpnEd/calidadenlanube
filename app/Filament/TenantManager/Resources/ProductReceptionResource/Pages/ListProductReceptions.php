<?php

namespace App\Filament\TenantManager\Resources\ProductReceptionResource\Pages;

use App\Filament\TenantManager\Resources\ProductReceptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductReceptions extends ListRecords
{
    protected static string $resource = ProductReceptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
