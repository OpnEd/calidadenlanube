<?php

namespace App\Filament\Resources\Quality\Training\CourseResource\Pages;

use App\Filament\Resources\Quality\Training\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Quality\Training\Enrollment;
use Filament\Notifications\Notification;
use App\Filament\Resources\Quality\Training\EnrollmentResource;
use Filament\Facades\Filament;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;


    protected function getHeaderActions(): array
    {
        // 1. Check if the logged-in user is already enrolled in this course
        $enrollment = Enrollment::query()
            ->where('course_id', $this->record->id)
            ->where('user_id', auth()->id())
            ->first();

        // 2. Fetch the first active lesson of the course
        $firstLesson = $this->record->lessons()
            ->where('lessons.active', true)
            ->first();

        $actions = [
            Actions\Action::make('backToIndex')
                ->label('Lista de cursos')
                ->icon('phosphor-rewind-circle')
                ->color('info')
                ->url(fn() => static::$resource::getUrl('index')),
        ];

        if ($enrollment) {
            // User IS enrolled -> Show button to continue/start the course
            if ($firstLesson) {
                $actions[] = Actions\Action::make('takeCourse')
                    ->label('Ir al curso')
                    ->icon('heroicon-o-academic-cap')
                    ->color('warning')
                    ->url(fn() => EnrollmentResource::getUrl('lesson', [
                        'record' => $enrollment->getKey(),
                        'lesson' => $firstLesson->getKey(),
                    ]));
            }
        } else {
            // User is NOT enrolled -> Directly enroll them and redirect
            $actions[] = Actions\Action::make('enroll')
                ->label('Inscribirme a este curso')
                ->icon('heroicon-o-user-plus')
                ->color('warning')
                ->action(function () use ($firstLesson) {
                    $newEnrollment = Enrollment::create([
                        'course_id'  => $this->record->id,
                        'user_id'    => auth()->id(),
                        'team_id'    => Filament::getTenant()?->id,
                        'status'     => 'in_progress',
                        'started_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Te has inscrito correctamente al curso')
                        ->success()
                        ->send();

                    // Redirect straight to the first lesson if it exists, otherwise refresh the page
                    if ($firstLesson) {
                        return redirect()->to(EnrollmentResource::getUrl('lesson', [
                            'record' => $newEnrollment->getKey(),
                            'lesson' => $firstLesson->getKey(),
                        ]));
                    }

                    return redirect()->to(static::$resource::getUrl('view', ['record' => $this->record->id]));
                });
        }

        $actions[] = Actions\EditAction::make();

        return $actions;
    }

    public function getTitle(): string
    {
        return $this->record->title ?? 'Detalles del curso';
    }
}
