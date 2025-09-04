<?php

namespace App\Filament\TenantManager\Resources\Training\LessonResource\Pages;

use App\Filament\TenantManager\Resources\Training\LessonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLesson extends EditRecord
{
    protected static string $resource = LessonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
