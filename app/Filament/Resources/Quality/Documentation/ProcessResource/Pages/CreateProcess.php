<?php

namespace App\Filament\Resources\Quality\Documentation\ProcessResource\Pages;

use App\Filament\Resources\Quality\Documentation\ProcessResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateProcess extends CreateRecord
{
    protected static string $resource = ProcessResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()?->id;

        return $data;
    }
}

