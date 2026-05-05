<?php

namespace App\Filament\Resources\RecipebookResource\Pages;

use App\Filament\Resources\RecipebookResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Gate;
use App\Models\Recipebook;

class ViewRecipebook extends ViewRecord
{
    protected static string $resource = RecipebookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('phosphor-pencil-simple-line')
                ->visible(fn(): bool => Gate::allows('update', Recipebook::class)),
        ];
    }
}
