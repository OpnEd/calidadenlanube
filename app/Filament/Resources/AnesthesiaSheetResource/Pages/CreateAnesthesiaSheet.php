<?php

namespace App\Filament\Resources\AnesthesiaSheetResource\Pages;

use App\Filament\Resources\AnesthesiaSheetResource;
use App\Models\AnesthesiaSheet;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAnesthesiaSheet extends CreateRecord
{
    protected static string $resource = AnesthesiaSheetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()->id;
        $data['user_id'] = auth()->id();
        $data['code'] = AnesthesiaSheet::generateAnesthesiaSheetConsecutive();
        return $data;
    }

    /* protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Hoja de anestesia abierta')
            ->body('La hoja de anestesia ha sido abierta exitosamente, ahora puedes diligenciarla');
    } */
}
