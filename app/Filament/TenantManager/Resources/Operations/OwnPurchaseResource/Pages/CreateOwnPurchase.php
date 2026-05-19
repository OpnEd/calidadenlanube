<?php

namespace App\Filament\TenantManager\Resources\Operations\OwnPurchaseResource\Pages;

use App\Filament\TenantManager\Resources\Operations\OwnPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOwnPurchase extends CreateRecord
{
    protected static string $resource = OwnPurchaseResource::class;
}
