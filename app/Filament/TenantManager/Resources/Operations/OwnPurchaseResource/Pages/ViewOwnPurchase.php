<?php

namespace App\Filament\TenantManager\Resources\Operations\OwnPurchaseResource\Pages;

use App\Filament\TenantManager\Resources\Operations\OwnPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOwnPurchase extends ViewRecord
{
    protected static string $resource = OwnPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
