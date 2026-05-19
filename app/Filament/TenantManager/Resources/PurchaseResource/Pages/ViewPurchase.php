<?php

namespace App\Filament\TenantManager\Resources\PurchaseResource\Pages;

use App\Filament\TenantManager\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redirect;
use App\Models\Purchase;
use App\Helpers\PermissionVerificationHelper;
use Illuminate\Support\Facades\Gate;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createDispatch')
                ->label('Despachar pedido')
                ->icon('phosphor-truck')
                ->visible(fn (): bool => Gate::allows('create-dispatch', $this->record))
                ->action(function (Model $record, array $data): void {
                    // Verificar si todos los PurchaseItems están enlistados
                    $notEnlisted = $record->items()->where('enlisted', '!=', 1)->exists();

                    if ($notEnlisted) {
                        \Filament\Notifications\Notification::make()
                                ->title('Falta verificar productos')
                                ->color('danger')
                                ->send();
                        return;
                    }

                    // Usar el servicio para crear el Dispatch
                    $dispatch = app(\App\Services\DispatchService::class)
                        ->createFromPurchase($record);

                    // Redirigir al edit del Dispatch recién creado
                    Redirect::to(
                        \App\Filament\TenantManager\Resources\DispatchResource::getUrl('edit', ['record' => $dispatch->id])
                    );
                })
                ->requiresConfirmation()
                ->color('info'),
        ];
    }
}
