<?php

namespace App\Filament\Resources\Quality\Training\EnrollmentResource\Pages;

use App\Filament\Resources\Quality\Training\EnrollmentResource;
use App\Models\Quality\Training\Enrollment;
use App\Filament\Resources\Quality\Training\ModuleResource;
use App\Filament\Resources\Quality\Training\CourseResource;
use Filament\Actions\Action;
use App\Models\Quality\Training\Lesson;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Actions;

class LessonView extends ViewRecord
{
    use InteractsWithRecord;

    protected static string $resource = EnrollmentResource::class;

    protected static string $view = 'filament.pages.quality.lesson-view';

    public Enrollment $enrollment;

    public Lesson $lesson;

    public function mount(int | string $record): void
    {
        $this->enrollment = $this->resolveRecord($record);
        abort_unless($this->enrollment, 404, 'Matrícula no encontrada');

        $this->record = $this->enrollment;
        $this->record->loadMissing('course');
        $this->authorizeEnrollmentAccess($this->enrollment);
        $this->lesson = $this->resolveLesson();
    }

    public function getTitle(): string
    {
        return 'Lección: ' . $this->lesson->title ?? 'Leccion';
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        /* $enrollment = $this->resolveEnrollment();
        
        if ($enrollment) {
            $assessment = $this->lesson->assessment;

            if ($assessment) {
                $actions[] = Action::make('realizar-assessment')
                    ->label('Presentar evaluación')
                    ->icon('heroicon-o-academic-cap')
                    ->color('warning')
                    ->url(fn (): string => EnrollmentResource::getUrl('lesson', [
                        'record' => $enrollment->getKey(),
                        'lesson' => $this->lesson->getKey(),
                    ]));
            } else {
                $actions[] = Action::make('no-assessment')
                    ->label('No hay evaluación para esta leccion')
                    ->disabled();
            }
        } else {
            $actions[] = Action::make('no-enrolled')
                ->label('Debe inscribirse para realizar la evaluación')
                ->disabled();
        } */

        // Navigation and edit actions
        $module = $this->lesson->module ?? null;
        $course = $module?->course ?? null;

        $actions[] = Actions\Action::make('backToModule')
            ->label('Módulo')
            ->icon('phosphor-rewind-circle')
            ->color('success')
            ->url(fn () => ModuleResource::getUrl('view', ['record' => $module?->id]))
            ->disabled(! $module?->id);

        $actions[] = Actions\Action::make('backToCourse')
            ->label('Curso')
            ->icon('phosphor-rewind-circle')
            ->color('info')
            ->url(fn () => CourseResource::getUrl('view', ['record' => $course?->id]))
            ->disabled(! $course?->id);

        $actions[] = Actions\EditAction::make()
            ->hidden(fn () => ! auth()->user()?->can('update', $this->record));

        return $actions;
    }

    protected function resolveEnrollment(): ?Enrollment
    {
        $user = auth()->user();
        $courseId = $this->record->course_id;
        $tenantId = Filament::getTenant()?->id;
        //dd($this->record);

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

    public function getLesson(): Lesson
    {
        return $this->lesson;
    }

    protected function authorizeEnrollmentAccess(Enrollment $enrollment): void
    {
        $user = Auth::user();
        $tenant = Filament::getTenant();

        abort_unless($user, 401, 'Usuario no autenticado');
        abort_unless($enrollment, 404, 'Matricula no encontrada');

        abort_unless(
            $enrollment->user_id === $user->id,
            403,
            'No tienes permiso para acceder a esta matricula'
        );

        abort_unless(
            $enrollment->team_id === $tenant?->id,
            403,
            'Matricula no valida para este equipo'
        );
    }

    protected function resolveLesson(): Lesson
    {
        $lessonParameter = request()->route('lesson');
        $lessonId = $lessonParameter instanceof Lesson
            ? $lessonParameter->getKey()
            : (is_array($lessonParameter)
                ? ($lessonParameter['id'] ?? $lessonParameter['lesson'] ?? null)
                : $lessonParameter);

        abort_unless(is_string($lessonId) || is_int($lessonId), 404);

        $lesson = Lesson::query()
            ->with(['module.course', 'assessment.questions.questionOptions'])
            ->findOrFail($lessonId);

        abort_unless($lesson->module?->course_id === $this->record->course_id, 403);

        return $lesson;
    }
}
