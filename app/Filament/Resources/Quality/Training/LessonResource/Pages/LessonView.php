<?php

namespace App\Filament\Resources\Quality\Training\LessonResource\Pages;

use App\Filament\Resources\Quality\Training\EnrollmentResource;
use App\Filament\Resources\Quality\Training\LessonResource;
use App\Models\Quality\Training\Enrollment;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\ViewRecord;

class LessonView extends ViewRecord
{
    use InteractsWithRecord;

    protected static string $resource = LessonResource::class;

    protected static string $view = 'filament.pages.quality.lesson-view';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        $enrollment = $this->resolveEnrollment();

        if ($enrollment) {
            $assessment = $this->record->assessment;

            if ($assessment) {
                $actions[] = Action::make('realizar-assessment')
                    ->label('Presentar evaluacion')
                    ->icon('heroicon-o-academic-cap')
                    ->color('warning')
                    ->url(fn (): string => EnrollmentResource::getUrl('lesson', [
                        'record' => $enrollment->getKey(),
                        'lesson' => $this->record->getKey(),
                    ]));
            } else {
                $actions[] = Action::make('no-assessment')
                    ->label('No hay assessment para esta leccion')
                    ->disabled();
            }
        } else {
            $actions[] = Action::make('no-enrolled')
                ->label('Debe inscribirse para realizar la evaluacion')
                ->disabled();
        }

        return $actions;
    }

    protected function resolveEnrollment(): ?Enrollment
    {
        $user = auth()->user();
        $courseId = $this->record->module?->course_id;
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
}
