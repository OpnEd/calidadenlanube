<?php

namespace App\Filament\Resources\Quality\Training\EnrollmentResource\Pages;

use App\Filament\Resources\Quality\Training\EnrollmentResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\ViewRecord;

class CourseOverview extends ViewRecord
{
    use InteractsWithRecord;

    protected static string $resource = EnrollmentResource::class;

    protected static string $view = 'filament.pages.quality.course-overview';

    public function getTitle(): string
    {
        $this->record->loadMissing('course');

        return $this->record->course?->title ?: 'Matrícula';
    }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->loadMissing('course');
    }
}
