<?php

namespace App\Filament\TenantManager\Resources\Operations\InvoiceResource\Pages;

use App\Filament\TenantManager\Resources\Operations\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;
}
