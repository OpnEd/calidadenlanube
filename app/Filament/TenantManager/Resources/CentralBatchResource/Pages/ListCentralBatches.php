<?php

namespace App\Filament\TenantManager\Resources\CentralBatchResource\Pages;

use App\Filament\TenantManager\Resources\CentralBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCentralBatches extends ListRecords
{
    protected static string $resource = CentralBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Registrar Lote'),
        ];
    }
}
