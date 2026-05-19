<?php

namespace App\Filament\TenantManager\Resources\CentralBatchResource\Pages;

use App\Filament\TenantManager\Resources\CentralBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Notifications\Notification;
use Illuminate\Support\Str;

class CreateCentralBatch extends CreateRecord
{
    protected static string $resource = CentralBatchResource::class;

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->color('success')
            ->title('Registro de lote')
            ->body(Str::markdown('La información del **lote** fue registrada con éxito!'))
            ->icon('phosphor-barcode')
            ->iconColor('success')
            ->size('4xl');
    }
}
