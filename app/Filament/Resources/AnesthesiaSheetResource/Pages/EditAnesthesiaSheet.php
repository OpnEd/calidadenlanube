<?php

namespace App\Filament\Resources\AnesthesiaSheetResource\Pages;

use App\Filament\Resources\AnesthesiaSheetResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAnesthesiaSheet extends EditRecord
{
    protected static string $resource = AnesthesiaSheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\Action::make('close')
                ->label(__('clinic.confirm_close_sheet'))
                ->visible(fn ($record) => auth()->user()->can('close', $record))
                ->action(function ($record) {
                    $record->status = 'closed';
                    $record->closed_at = now();
                    $record->save();
                    Notification::make()
                        ->title(__('clinic.anesthesia_sheet_closed'))
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->icon('heroicon-o-check-circle')
                ->tooltip(__('clinic.close_anesthesia_sheet'))
                ->color('success'),
            Actions\Action::make('cancel')
                ->label(__('clinic.cancel_sheet'))
                ->action(function ($record) {
                    $record->status = 'canceled';
                    $record->save();
                    Notification::make()
                        ->title(__('clinic.anesthesia_sheet_canceled'))
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}
