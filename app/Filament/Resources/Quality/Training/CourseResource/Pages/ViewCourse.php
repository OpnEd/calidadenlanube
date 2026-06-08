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
                ->label('Volver a la lista de cursos')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => static::$resource::getUrl('index')),
            Actions\EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Detalles del curso';
    }
}
