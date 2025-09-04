<?php

namespace App\Filament\TenantManager\Resources\Training\CourseResource\Pages;

use App\Filament\TenantManager\Resources\Training\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
