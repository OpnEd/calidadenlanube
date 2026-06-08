<?php

namespace App\Filament\Resources\Quality\Training\AssessmentResource\Pages;

use App\Filament\Resources\Quality\Training\AssessmentResource;
use App\Models\Quality\Training\Lesson;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditAssessment extends EditRecord
{
    protected static string $resource = AssessmentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $lesson = Lesson::query()
            ->with('module:id,course_id')
            ->whereHas('module.course', fn ($query) => $query->ownedByTeam(Filament::getTenant()?->id))
            ->findOrFail($data['lesson_id']);

        $data['module_id'] = $lesson->module_id;
        $data['course_id'] = $lesson->module?->course_id;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
