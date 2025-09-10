<?php

namespace App\Filament\TenantManager\Resources\ProductReceptionItemResource\Pages;

use App\Filament\TenantManager\Resources\ProductReceptionItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductReceptionItem extends EditRecord
{
    protected static string $resource = ProductReceptionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
