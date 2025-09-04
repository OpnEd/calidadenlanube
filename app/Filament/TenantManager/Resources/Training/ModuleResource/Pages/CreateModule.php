<?php

namespace App\Filament\TenantManager\Resources\Training\ModuleResource\Pages;

use App\Filament\TenantManager\Resources\Training\ModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateModule extends CreateRecord
{
    protected static string $resource = ModuleResource::class;
}
