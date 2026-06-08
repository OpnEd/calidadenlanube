<?php

namespace App\Filament\Resources\Quality\Training\LessonResource\Pages;

use App\Filament\Resources\Quality\Training\CourseResource;
use App\Filament\Resources\Quality\Training\ModuleResource;
use App\Filament\Resources\Quality\Training\LessonResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLesson extends ViewRecord
{
    protected static string $resource = LessonResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('backToModule')
                ->label('Volver al módulo')
                ->icon('heroicon-o-arrow-left')
                ->color('success')
                ->url(fn () => ModuleResource::getUrl('view', ['record' => $this->record->module->id])),

            Actions\Action::make('backToCourse')
                ->label('Volver al curso')
                ->icon('heroicon-o-arrow-left')
                ->color('info')
                ->url(fn () => CourseResource::getUrl('view', ['record' => $this->record->module->course->id])),

            Actions\EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Detalles de la lección';
    }
}
