<?php

namespace App\Filament\Resources\Quality\Training\ModuleResource\Pages;

use App\Filament\Resources\Quality\Training\ModuleResource;
use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Module;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;

class EditModule extends EditRecord
{
    protected static string $resource = ModuleResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $course = Course::query()->findOrFail((int) $data['course_id']);

        Gate::authorize('createForCourse', [Module::class, $course]);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
