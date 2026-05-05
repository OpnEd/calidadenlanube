<?php

namespace App\Filament\Resources\ProductReceptionResource\Pages;

use App\Filament\Resources\ProductReceptionResource;
use App\Models\ProductReception;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ViewRecord;

class ViewProductReception extends ViewRecord
{
    protected static string $resource = ProductReceptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar')
                ->visible(fn (): bool => ! $this->record->isDone()),
            Action::make('download_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->url(function (ProductReception $record): string {
                    $tenantId = Filament::getTenant()?->id;

                    if (! $tenantId) {
                        return '#';
                    }

                    return route('product-receptions.download', [
                        'tenant' => $tenantId,
                        'productReception' => $record,
                    ]);
                })
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled(Filament::getTenant()?->id)),
        ];
    }
}
