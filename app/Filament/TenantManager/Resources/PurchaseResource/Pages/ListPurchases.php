<?php

namespace App\Filament\TenantManager\Resources\PurchaseResource\Pages;

use App\Filament\TenantManager\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
