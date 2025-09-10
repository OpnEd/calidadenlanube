<?php

namespace App\Filament\TenantManager\Resources\StockResource\Pages;

use App\Filament\TenantManager\Resources\StockResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;
}
