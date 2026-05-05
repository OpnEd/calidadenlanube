<?php

namespace App\Filament\Resources\Quality\Improvement\ActionResource\Pages;

use App\Filament\Resources\Quality\Improvement\ActionResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateAction extends CreateRecord
{
    protected static string $resource = ActionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()?->id;

        return $data;
    }
}

