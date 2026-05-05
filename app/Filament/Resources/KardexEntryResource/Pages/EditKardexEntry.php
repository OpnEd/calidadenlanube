<?php

namespace App\Filament\Resources\KardexEntryResource\Pages;

use App\Filament\Resources\KardexEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKardexEntry extends EditRecord
{
    protected static string $resource = KardexEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
