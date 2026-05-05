<?php

namespace App\Filament\Resources\AnesthesiaSheetResource\Pages;

use App\Filament\Resources\AnesthesiaSheetResource;
use App\Models\AnesthesiaSheet;
use App\Models\Recipebook;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class ListAnesthesiaSheets extends ListRecords
{
    protected static string $resource = AnesthesiaSheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_anesthesia_sheet')
                ->label(__('clinic.open_anesthesia_sheet'))
                ->icon('phosphor-file-plus')
                ->modalHeading(__('clinic.new_anesthesia_sheet'))
                ->form([
                    Select::make('recipebook_id')
                        ->label(__('clinic.recipebook'))
                        ->relationship(
                            name: 'recipebook',
                            titleAttribute: 'consecutive',
                            modifyQueryUsing: fn($query) => $query
                                ->where('status', 'available')
                                ->orderBy('consecutive')
                        )
                        ->searchable()
                        ->getOptionLabelFromRecordUsing(
                            fn($record) =>
                            __('clinic.recipe_number') . " {$record->consecutive}"
                        )
                        ->unique(table: 'anesthesia_sheets', column: 'recipebook_id')
                        ->required(),
                ])
                ->action(function (array $data, Action $action) {
                    // DEBUG: Ver qué datos llegan del formulario
                    //Log::info('Hoja de anestesia data', ['data' => $data]);

                    // Validar que exista el producto
                    if (empty($data['recipebook_id'])) {
                        Notification::make()
                            ->title(__('clinic.select_valid_recipe'))
                            ->icon('phosphor-x-circle')
                            ->color('danger')
                            ->send();
                        return;
                    }

                    try {
                        // Transacción para crear la AnesthesiaSheet y actualizar Recipebook de forma atómica
                        $sheet = DB::transaction(function () use ($data) {
                            // Bloquea la fila para evitar condiciones de carrera
                            $recipebook = Recipebook::where('id', $data['recipebook_id'])->lockForUpdate()->first();

                            if (! $recipebook) {
                                Notification::make()
                                    ->title(__('clinic.recipebook_not_found'))
                                    ->icon('phosphor-x-circle')
                                    ->color('danger')
                                    ->send();
                            }

                            // Si tu lógica requiere impedir reuso de recipebook:
                            if ($recipebook->status === 'in_use') {
                                Notification::make()
                                    ->title(__('clinic.recipebook_already_in_use'))
                                    ->icon('phosphor-x-circle')
                                    ->color('danger')
                                    ->send();
                            }

                            // Crea la hoja
                            $sheet = AnesthesiaSheet::create([
                                'code'          => AnesthesiaSheet::generateAnesthesiaSheetConsecutive(),
                                'team_id'       => Filament::getTenant()->id,
                                'user_id'       => Auth::user()->id,
                                'recipebook_id'     => $data['recipebook_id'],
                                'customer_id'   => $data['customer_id'] ?? null,
                                'pet_id'        => $data['pet_id'] ?? null,
                                'surgeon_id'    => $data['surgeon_id'] ?? null,
                                'anamnesis'     => $data['anamnesis'] ?? null,
                                'anesthesia_notes' => $data['anesthesia_notes'] ?? null,
                                'anesthesia_start_time' => now(),
                                'anesthesia_end_time' => null,
                            ]);

                            // Actualiza el Recipebook
                            $recipebook->update([
                                'issue_date' => now(),
                                'user_id'    => Auth::user()->id,
                                'status'     => 'in_use',
                            ]);

                            return $sheet;
                        }, 5);

                        Notification::make()
                            ->title(__('clinic.successful_opening'))
                            ->body(__('clinic.can_continue_editing_the_anesthesia_sheet'))
                            ->icon('phosphor-check')
                            ->success()
                            ->send();

                        // Redirigir al formulario de edición de este Sale (donde ItemsRelationManager mostrará el item)
                        return Redirect::to(
                            AnesthesiaSheetResource::getUrl('edit', ['record' => $sheet->id])
                        );
                    } catch (Exception $e) {
                        //Log::error('Error creating anesthesia sheet', ['error' => $e->getMessage(), 'data' => $data]);

                        Notification::make()
                            ->title(__('clinic.failed_to_open') ?? 'No se pudo abrir la hoja')
                            ->body($e->getMessage())
                            ->icon('phosphor-x-circle')
                            ->danger()
                            ->send();

                        // No redirigimos; Filament mantendrá el modal abierto mostrando la notificación.
                        return;
                    }
                })
                ->requiresConfirmation()
                ->visible(fn(): bool => Gate::allows('create', AnesthesiaSheet::class)),
        ];
    }
}
