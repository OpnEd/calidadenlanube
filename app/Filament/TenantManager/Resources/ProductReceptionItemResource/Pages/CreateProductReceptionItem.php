<?php

namespace App\Filament\TenantManager\Resources\ProductReceptionItemResource\Pages;

use App\Filament\TenantManager\Resources\ProductReceptionItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductReceptionItem extends CreateRecord
{
    protected static string $resource = ProductReceptionItemResource::class;
}
