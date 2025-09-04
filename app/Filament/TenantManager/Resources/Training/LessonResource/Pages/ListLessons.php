<?php

namespace App\Filament\TenantManager\Resources\Training\LessonResource\Pages;

use App\Filament\TenantManager\Resources\Training\LessonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLessons extends ListRecords
{
    protected static string $resource = LessonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
