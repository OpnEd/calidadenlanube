<?php

namespace App\Filament\Resources\Quality\Training\LessonResource\Pages;

use App\Filament\Resources\Quality\Training\CourseResource;
use App\Filament\Resources\Quality\Training\EnrollmentResource;
use App\Filament\Resources\Quality\Training\ModuleResource;
use App\Filament\Resources\Quality\Training\LessonResource;
use App\Models\Quality\Training\Enrollment;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewLesson extends ViewRecord
{
    protected static string $resource = LessonResource::class;

    protected function getHeaderActions(): array
    {
        $enrollment = $this->resolveEnrollment();

        return [

            Actions\Action::make('backToModule')
                ->label('Módulo')
                ->icon('phosphor-rewind-circle')
                ->color('success')
                ->url(fn () => ModuleResource::getUrl('view', ['record' => $this->record->module->id])),

            Actions\Action::make('backToCourse')
                ->label('Curso')
                ->icon('phosphor-rewind-circle')
                ->color('info')
                ->url(fn () => CourseResource::getUrl('view', ['record' => $this->record->module->course->id])),

            Actions\Action::make('takeLesson')
                ->label('Tomar lección')
                ->icon('heroicon-o-academic-cap')
                ->color('warning')
                ->url(fn (): ?string => $enrollment
                    ? EnrollmentResource::getUrl('lesson', [
                        'record' => $enrollment->getKey(),
                        'lesson' => $this->record->getKey(),
                    ])
                    : null)
                ->disabled(! $enrollment),

            Actions\EditAction::make(),
        ];
    }

    protected function resolveEnrollment(): ?Enrollment
    {
        $user = Auth::user();
        $courseId = $this->record?->module?->course_id;
        $tenantId = Filament::getTenant()?->id;

        if (! $user || ! $courseId || ! $tenantId) {
            return null;
        }

        return Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('team_id', $tenantId)
            ->orderByRaw(
                'case when status = ? then 0 when status = ? then 1 else 2 end',
                [Enrollment::STATUS_IN_PROGRESS, Enrollment::STATUS_NOT_STARTED]
            )
            ->latest('id')
            ->first();
    }

    public function getTitle(): string
    {
        return 'Detalles de la lección';
    }
}
