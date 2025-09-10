<?php

namespace App\Filament\TenantManager\Resources\ProductReceptionResource\Pages;

use App\Filament\TenantManager\Resources\ProductReceptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductReception extends EditRecord
{
    protected static string $resource = ProductReceptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
