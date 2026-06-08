<?php

namespace App\Filament\Resources\Quality\Training\ModuleResource\Pages;

use App\Filament\Resources\Quality\Training\ModuleResource;
use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Module;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Gate;

class CreateModule extends CreateRecord
{
    protected static string $resource = ModuleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $course = Course::query()->findOrFail((int) $data['course_id']);

        Gate::authorize('createForCourse', [Module::class, $course]);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
