<?php

namespace App\Filament\Resources\Quality\Training\AssessmentResource\Pages;

use App\Filament\Resources\Quality\Training\AssessmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssessments extends ListRecords
{
    protected static string $resource = AssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
