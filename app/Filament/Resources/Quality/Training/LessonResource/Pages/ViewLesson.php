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
use Filament\Notifications\Notification;

class ViewLesson extends ViewRecord
{
    protected static string $resource = LessonResource::class;

    protected function getHeaderActions(): array
    {
        $enrollment = $this->resolveEnrollment();

        $actions = [
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
        ];

        if ($enrollment) {
            $actions[] = Actions\Action::make('takeLesson')
                ->label('Tomar lección')
                ->icon('heroicon-o-academic-cap')
                ->color('warning')
                ->url(fn (): ?string => EnrollmentResource::getUrl('lesson', [
                        'record' => $enrollment->getKey(),
                        'lesson' => $this->record->getKey(),
                    ]));
        } else {
            $actions[] = Actions\Action::make('enroll')
                ->label('Inscribirme a este curso')
                ->icon('heroicon-o-user-plus')
                ->color('warning')
                ->action(function () {
                $course = $this->record->module?->course;

                if (! $course) {
                    return;
                }

                // Create the enrollment using your standard parameters
                $newEnrollment = Enrollment::create([
                    'course_id'  => $course->id,
                    'user_id'    => auth()->id(),
                    'team_id'    => Filament::getTenant()?->id,
                    'status'     => 'in_progress',
                    'started_at' => now(),
                ]);

                Notification::make()
                    ->title('Te has inscrito correctamente al curso')
                    ->success()
                    ->send();

                // Instantly redirect them to start taking the current lesson
                return redirect()->to(EnrollmentResource::getUrl('lesson', [
                    'record' => $newEnrollment->getKey(),
                    'lesson' => $this->record->getKey(),
                ]));
            });
    }

        $actions[] = Actions\EditAction::make();

        return $actions;
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
