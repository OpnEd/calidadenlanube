<?php

namespace App\Filament\TenantManager\Resources\CentralBatchResource\Pages;

use App\Filament\TenantManager\Resources\CentralBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCentralBatch extends EditRecord
{
    protected static string $resource = CentralBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
