<?php

namespace App\Filament\Resources\Quality\Training\EnrollmentResource\Pages;

use App\Filament\Resources\Quality\Training\EnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
