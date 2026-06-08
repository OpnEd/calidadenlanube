<?php

namespace App\Filament\Resources\Quality\Training\CourseResource\Pages;

use App\Filament\Resources\Quality\Training\CourseResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()?->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
