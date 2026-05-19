<?php

namespace App\Filament\TenantManager\Resources\ProductReceptionResource\Pages;

use App\Filament\TenantManager\Resources\ProductReceptionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProductReception extends CreateRecord
{
    protected static string $resource = ProductReceptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['team_id'] = null;
        return $data;
    }
}
