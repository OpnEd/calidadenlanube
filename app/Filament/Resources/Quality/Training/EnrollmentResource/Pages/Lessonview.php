<?php

namespace App\Filament\Resources\Quality\Training\EnrollmentResource\Pages;

use App\Filament\Resources\Quality\Training\EnrollmentResource;
use App\Models\Quality\Training\Enrollment;
use App\Models\Quality\Training\Lesson;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Actions;

class Lessonview extends ViewRecord
{
    use InteractsWithRecord;

    protected static string $resource = EnrollmentResource::class;

    protected static string $view = 'filament.pages.quality.lesson-view';

    public Lesson $lesson;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backToCourse')
                ->label('Volver al curso')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => static::$resource::getUrl('view', ['record' => $this->record])),
        ];
    }

    public function getTitle(): string
    {
        return 'Lección: ' . $this->lesson->title ?? 'Leccion';
    }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->loadMissing('course');

        $this->authorizeEnrollmentAccess($this->record);
        $this->lesson = $this->resolveLesson();
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
