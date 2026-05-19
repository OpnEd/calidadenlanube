<?php

namespace App\Filament\TenantManager\Resources\EnvironmentalRecordResource\Pages;

use App\Filament\TenantManager\Resources\EnvironmentalRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use App\Notifications\Notification;


class EditEnvironmentalRecord extends EditRecord
{
    protected static string $resource = EnvironmentalRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar registro')
                ->successNotification(fn (): Notification => Notification::make()
                    ->color('success')
                    ->success()
                    ->title('Registro ambiental eliminado')
                    ->body(Str::markdown('El **registro ambiental** fue eliminado correctamente!'))
                    ->icon('phosphor-trash')
                    ->iconColor('success')
                    ->size('4xl'))
        ];
    }
}
