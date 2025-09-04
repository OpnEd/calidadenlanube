<?php

namespace App\Filament\TenantManager\Resources\Training\ModuleResource\Pages;

use App\Filament\TenantManager\Resources\Training\ModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModules extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
