<?php

namespace App\Filament\Resources\Quality\Improvement\PlanResource\Pages;

use App\Filament\Resources\Quality\Improvement\PlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

