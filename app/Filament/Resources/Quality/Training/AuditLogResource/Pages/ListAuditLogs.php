<?php

namespace App\Filament\Resources\Quality\Training\AuditLogResource\Pages;

use App\Filament\Resources\Quality\Training\AuditLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for audit logs (read-only)
        ];
    }
}
