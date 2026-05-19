<?php

namespace App\Filament\TenantManager\Resources\CentralProductPriceResource\Pages;

use App\Filament\TenantManager\Resources\CentralProductPriceResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Actions\CreateAction;
use App\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\CreateAction as TablesCreateAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Htmlable;

class ListCentralProductPrices extends ListRecords
{
    protected static string $resource = CentralProductPriceResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Lista de precios');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Registrar precio')
                ->modalHeading(function (array $data): string {
                    $productName = filled($data['product_id'] ?? null)
                        ? Product::query()->whereKey($data['product_id'])->value('name')
                        : null;

                    return $productName
                        ? "Nuevo registro de precio: {$productName}"
                        : 'Nuevo registro de precio';
                })
                ->modalDescription('Registra el precio del producto.')
                ->modalSubmitActionLabel('Guardar precio')
                ->createAnother(false)
                ->successNotification(fn(): Notification => Notification::make()
                    ->color('success')
                    ->success()
                    ->title('Registro de precio creado')
                    ->body(Str::markdown('El **precio** fue guardado correctamente!'))
                    ->icon('phosphor-tag')
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
