<?php

namespace App\Filament\TenantManager\Resources\EnvironmentalRecordResource\Pages;

use App\Filament\TenantManager\Resources\EnvironmentalRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEnvironmentalRecord extends CreateRecord
{
    protected static string $resource = EnvironmentalRecordResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['team_id'] = null;
        return $data;
    }
}
