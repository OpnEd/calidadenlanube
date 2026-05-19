<?php

namespace App\Filament\TenantManager\Resources\Operations\InvoiceResource\Pages;

use App\Filament\TenantManager\Resources\Operations\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
