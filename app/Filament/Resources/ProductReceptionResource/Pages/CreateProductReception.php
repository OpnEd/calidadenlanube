<?php

namespace App\Filament\Resources\ProductReceptionResource\Pages;

use App\Filament\Resources\ProductReceptionResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateProductReception extends CreateRecord
{
    protected static string $resource = ProductReceptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = $data['team_id'] ?? Filament::getTenant()?->id;
        $data['user_id'] = $data['user_id'] ?? auth()->id();

        return $data;
    }
}
