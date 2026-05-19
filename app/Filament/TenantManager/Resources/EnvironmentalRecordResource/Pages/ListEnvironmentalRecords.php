<?php

namespace App\Filament\TenantManager\Resources\EnvironmentalRecordResource\Pages;

use App\Filament\TenantManager\Resources\EnvironmentalRecordResource;
use Filament\Actions;
use Filament\Actions\CreateAction;
use App\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\CreateAction as TablesCreateAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ListEnvironmentalRecords extends ListRecords
{
    protected static string $resource = EnvironmentalRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Registrar medicion')
                ->modalHeading('Nuevo registro ambiental')
                ->modalDescription('Registra la temperatura y la humedad del momento.')
                ->modalSubmitActionLabel('Guardar registro')
                ->createAnother(false)
                ->successNotification(fn (): Notification => Notification::make()
                    ->color('success')
                    ->success()
                    ->title('Registro ambiental creado')
                    ->body(Str::markdown('La **temperatura y humedad** fueron guardadas correctamente!'))
                    ->icon('phosphor-thermometer')
                    ->iconColor('success')
                    ->size('4xl'))
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = Auth::id();
                    $data['team_id'] = null;

                    return $data;
                }),
        ];
    }

    protected function configureCreateAction(CreateAction | TablesCreateAction $action): void
    {
        parent::configureCreateAction($action);

        if ($action instanceof CreateAction) {
            $action->url(null);
        }
    }
}
