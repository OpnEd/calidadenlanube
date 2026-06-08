<?php

namespace App\Livewire\Quality\Training;

use App\Models\Quality\Training\Assessment;
use App\Models\Quality\Training\AssessmentAttempt;
use App\Models\Quality\Training\Enrollment;
use App\Models\Quality\Training\Question;
use App\Services\Quality\AssessmentService;
use Filament\Notifications\Notification;
use Livewire\Component;

class AssessmentComponent extends Component
{
    public Assessment $assessment;

    public Enrollment $enrollment;

    public ?AssessmentAttempt $currentAttempt = null;

    public array $userAnswers = [];

    public bool $isSubmitting = false;

    public bool $showResults = false;

    public ?array $results = null;

    public ?int $remainingAttempts = null;

    protected AssessmentService $assessmentService;

    public function mount(Assessment $assessment, Enrollment $enrollment): void
    {
        $this->assessment = $assessment->loadMissing('questions.questionOptions');
        $this->enrollment = $enrollment;
        $this->assessmentService = app(AssessmentService::class);
        $this->syncRemainingAttempts();

        $this->startAttempt();
    }

    public function startAttempt(): void
    {
        try {
            $this->currentAttempt = $this->assessmentService->startAttempt(
                assessment: $this->assessment,
                enrollment: $this->enrollment,
                user: auth()->user(),
            );

            $this->userAnswers = [];
            $this->showResults = false;
            $this->results = null;
            $this->syncRemainingAttempts();

            Notification::make()
                ->title('Evaluacion iniciada')
                ->body('Tienes ' . ($this->assessment->duration_minutes ?? 'sin limite') . ' minutos para completarla.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->currentAttempt = null;
        }
    }

    public function submitAssessment(): void
    {
        if (! $this->currentAttempt) {
            return;
        }

        $this->isSubmitting = true;

        try {
            $requiredQuestions = $this->assessment->questions
                ->filter(fn (Question $question) => $question->isRequired());

            $unansweredRequired = $requiredQuestions
                ->filter(fn (Question $question) => ! $this->hasAnswerForQuestion(
                    $question,
                    $this->userAnswers[$question->id] ?? null
                ));

            if ($unansweredRequired->isNotEmpty()) {
                Notification::make()
                    ->title('Validacion')
                    ->body('Debes responder todas las preguntas obligatorias.')
                    ->warning()
                    ->send();

                $this->isSubmitting = false;

                return;
            }

            $this->currentAttempt = $this->assessmentService->submitAttempt(
                attempt: $this->currentAttempt,
                answers: $this->userAnswers,
            );

            $this->currentAttempt = $this->assessmentService->gradeAttempt($this->currentAttempt);
            $this->results = $this->assessmentService->buildAttemptSummary($this->currentAttempt);
            $this->showResults = true;
            $this->syncRemainingAttempts();

            Notification::make()
                ->title('Evaluacion completada')
                ->body(
                    'Tu resultado es: '
                    . number_format($this->results['score'], 1)
                    . '/'
                    . number_format($this->results['max_score'], 1)
                )
                ->success()
                ->send();

            $this->dispatch(
                'assessment-completed',
                attemptId: $this->currentAttempt->id,
                score: $this->currentAttempt->score,
                passed: $this->currentAttempt->isPassed(),
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Error al procesar la evaluacion: ' . $e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function cancelAttempt(): void
    {
        if ($this->currentAttempt && ! $this->currentAttempt->isCompleted()) {
            $this->currentAttempt->delete();
        }

        $this->currentAttempt = null;
        $this->userAnswers = [];
        $this->showResults = false;
        $this->results = null;
        $this->syncRemainingAttempts();

        Notification::make()
            ->title('Evaluacion cancelada')
            ->body('Tu intento ha sido cancelado.')
            ->info()
            ->send();
    }

    private function hasAnswerForQuestion(Question $question, mixed $answer): bool
    {
        if ($question->isMultipleChoiceMultiple()) {
            return collect($answer ?? [])
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->isNotEmpty();
        }

        return trim((string) $answer) !== '';
    }

    private function syncRemainingAttempts(): void
    {
        $this->remainingAttempts = $this->assessmentService->getRemainingAttempts(
            $this->assessment,
            $this->enrollment,
            auth()->user(),
        );
    }

    public function render()
    {
        return view('livewire.quality.training.assessment-component', [
            'questions' => $this->assessment->questions,
            'questionsCount' => $this->assessment->questions->count(),
        ]);
    }
}
