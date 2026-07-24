<?php

namespace App\Filament\Resources\Quality\Training\ModuleResource\Pages;

use App\Filament\Resources\Quality\Training\CourseResource;
use App\Filament\Resources\Quality\Training\EnrollmentResource;
use App\Filament\Resources\Quality\Training\ModuleResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Quality\Training\Enrollment;
use Filament\Notifications\Notification;

class ViewModule extends ViewRecord
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        $course = $this->record->course;

        // 1. Resolve if the current user is enrolled in this module's course
        $enrollment = $course ? Enrollment::query()
            ->where('course_id', $course->id)
            ->where('user_id', auth()->id())
            ->first() : null;

        // 2. Fetch the first active lesson of this module to jump into
        $firstLesson = $this->record->lessons()
            ->where('active', true)
            ->first();

        $actions = [
            Actions\Action::make('backToCourse')
                ->label('Curso padre')
                ->icon('phosphor-rewind-circle')
                ->color('info')
                ->url(fn() => CourseResource::getUrl('view', ['record' => $this->record->course_id])),
        ];

        if ($enrollment) {
            // User IS enrolled -> Show button to start/continue taking the module
            if ($firstLesson) {
                $actions[] = Actions\Action::make('takeModule')
                    ->label('Iniciar módulo')
                    ->icon('heroicon-o-academic-cap')
                    ->color('warning')
                    ->url(fn() => EnrollmentResource::getUrl('lesson', [
                        'record' => $enrollment->getKey(),
                        'lesson' => $firstLesson->getKey(),
                    ]));
            }
        } else {
            // User is NOT enrolled -> Enroll directly and launch the first lesson
            $actions[] = Actions\Action::make('enroll')
                ->label('Inscribirme a este curso')
                ->icon('heroicon-o-user-plus')
                ->color('warning')
                ->action(function () use ($course, $firstLesson) {
                    if (! $course) {
                        return;
                    }

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

                    // Redirect to the first active lesson if available, or back to the course
                    if ($firstLesson) {
                        return redirect()->to(EnrollmentResource::getUrl('lesson', [
                            'record' => $newEnrollment->getKey(),
                            'lesson' => $firstLesson->getKey(),
                        ]));
                    }

                    return redirect()->to(CourseResource::getUrl('view', ['record' => $course->id]));
                });
        }

        $actions[] = Actions\EditAction::make();

        return $actions;
    }

    public function getTitle(): string
    {
        return 'Descripción del módulo';
    }
}
