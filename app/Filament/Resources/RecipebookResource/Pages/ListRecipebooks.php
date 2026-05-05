<?php

namespace App\Filament\Resources\RecipebookResource\Pages;

use App\Filament\Resources\RecipebookResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\CentralProductPrice;
use App\Models\Purchase;
use Illuminate\Support\Facades\Redirect;
use App\Filament\Resources\PurchaseResource;
use App\Models\Recipebook;
use Filament\Facades\Filament;

class ListRecipebooks extends ListRecords
{
    protected static string $resource = RecipebookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('clinic.create_recipe'))
                ->icon('phosphor-plus')
                ->tooltip(__('clinic.create_one_recipe')),
                //->visible(fn(): bool => Gate::allows('create', Recipebook::class)),
            Action::make('create_recipebook')
                ->label(__('clinic.create_recipebook'))
                ->icon('phosphor-file-arrow-up')
                ->modalHeading(__('Registro de un nuevo recetario'))
                ->form([
                    Forms\Components\TextInput::make('consecutive_start')
                        ->label(__('clinic.consecutive_start'))
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('consecutive_end')
                        ->label(__('clinic.consecutive_end'))
                        ->required()
                        ->numeric(),
                ])
                ->action(function (array $data, Action $action) {
                    // DEBUG: Ver qué datos llegan del formulario
                    //Log::info('Recetario data', ['data' => $data]);

                    // Validar que exista el producto
                    if (empty($data['consecutive_start']) || empty($data['consecutive_end'])) {
                        \Filament\Notifications\Notification::make()
                            ->title(__('clinic.must_enter_valid_consecutive_range'))
                            ->icon('phosphor-x-circle')
                            ->color('danger')
                            ->send();
                        return;
                    }

                    foreach (range($data['consecutive_start'], $data['consecutive_end']) as $consecutive) {
                        \App\Models\Recipebook::create([
                            'consecutive' => $consecutive,
                            'team_id' => Filament::getTenant()->id,
                        ]);
                    }

                    \Filament\Notifications\Notification::make()
                        ->title(__('clinic.successful_recording'))
                        ->icon('phosphor-check')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->tooltip(__('clinic.create_recipe_range')),
                //->visible(fn(): bool => Gate::allows('create', Recipebook::class)),
        ];
    }
}
