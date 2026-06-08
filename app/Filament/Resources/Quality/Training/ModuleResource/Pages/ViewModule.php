<?php

namespace App\Filament\Resources\Quality\Training\ModuleResource\Pages;

use App\Filament\Resources\Quality\Training\CourseResource;
use App\Filament\Resources\Quality\Training\ModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewModule extends ViewRecord
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backToCourse')
                ->label('Volver al curso')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => CourseResource::getUrl('view', ['record' => $this->record->course->id])),
            Actions\EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Detalles del módulo';
    }
}