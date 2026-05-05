<?php

namespace App\Filament\Resources\AnesthesiaSheetResource\Pages;

use App\Filament\Resources\AnesthesiaSheetResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewAnesthesiaSheet extends ViewRecord
{
    protected static string $resource = AnesthesiaSheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('cancel')
                ->label(__('clinic.cancel_sheet'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => $record->status !== 'canceled')
                ->requiresConfirmation()
                ->action(function ($record): void {
                    $record->status = 'canceled';
                    $record->save();

                    Notification::make()
                        ->title(__('clinic.anesthesia_sheet_canceled'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
