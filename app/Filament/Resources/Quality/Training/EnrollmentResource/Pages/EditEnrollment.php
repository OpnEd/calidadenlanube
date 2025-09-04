<?php

namespace App\Filament\Resources\Quality\Training\EnrollmentResource\Pages;

use App\Filament\Resources\Quality\Training\EnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnrollment extends EditRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
