<?php

namespace App\Filament\Resources\Quality\Improvement\FindingResource\Pages;

use App\Filament\Resources\Quality\Improvement\FindingResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateFinding extends CreateRecord
{
    protected static string $resource = FindingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()?->id;
        $data['reported_by'] = auth()->id();

        return $data;
    }
}

