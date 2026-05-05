<?php

namespace App\Filament\Resources\ProductReceptionResource\Pages;

use App\Filament\Resources\ProductReceptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductReception extends EditRecord
{
    protected static string $resource = ProductReceptionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['status']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
