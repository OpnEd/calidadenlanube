<?php

namespace App\Filament\Resources\Quality\Training\CourseResource\Pages;

use App\Filament\Resources\Quality\Training\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backToIndex')
                ->label('Lista de cursos')
                ->icon('phosphor-rewind-circle')
                ->url(fn () => static::$resource::getUrl('index')),
            Actions\EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Detalles del curso';
    }
}
