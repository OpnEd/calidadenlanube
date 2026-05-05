<?php

namespace App\Filament\Resources\RecipebookResource\Pages;

use App\Filament\Resources\RecipebookResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateRecipebook extends CreateRecord
{
    protected static string $resource = RecipebookResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()->id;
        $data['user_id'] = auth()->id();
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
            ->title('Recetario creado')
            ->body('El recetario ha sido creado exitosamente');
    } */
}
