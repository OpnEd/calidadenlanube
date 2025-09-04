<?php

namespace App\Filament\Resources\Quality\Training\EnrollmentResource\Pages;


use App\Filament\Resources\Quality\Training\EnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class Lessonview extends ViewRecord
{
    use InteractsWithRecord;
    protected static string $resource = EnrollmentResource::class;

    protected static string $view = 'filament.pages.quality.lessonview';
    
    
    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
