<?php

namespace App\Filament\Resources\Quality\Training\LessonResource\Pages;

use App\Filament\Resources\Quality\Training\LessonResource;
use App\Models\Quality\Training\Lesson;
use App\Models\Quality\Training\Module;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;

class EditLesson extends EditRecord
{
    protected static string $resource = LessonResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $module = Module::query()->findOrFail((int) $data['module_id']);

        Gate::authorize('createForModule', [Lesson::class, $module]);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
