<?php

namespace App\Filament\TenantManager\Resources\Operations\OwnPurchaseResource\Pages;

use App\Filament\TenantManager\Resources\Operations\OwnPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOwnPurchase extends EditRecord
{
    protected static string $resource = OwnPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
