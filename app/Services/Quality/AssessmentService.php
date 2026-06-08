<?php

namespace App\Services\Quality;

use App\Models\Quality\Training\Assessment;
use App\Models\Quality\Training\AssessmentAttempt;
use App\Models\Quality\Training\Enrollment;
use App\Models\Quality\Training\Question;
use App\Models\Quality\Training\UserAnswer;
use Illuminate\Support\Facades\DB;

class AssessmentService
{
    public function __construct(
        protected EnrollmentLessonService $enrollmentLessons
    ) {}

    public function startAttempt(Assessment $assessment, Enrollment $enrollment, $user): AssessmentAttempt
    {
        if ($user->id !== $enrollment->user_id) {
            throw new \RuntimeException('El usuario no esta inscrito en esta matricula.');
        }

        if (! $assessment->active) {
            throw new \RuntimeException('La evaluacion no esta activa.');
        }

        $lesson = $assessment->lesson()->with('module')->first();

        if (! $lesson) {
            throw new \RuntimeException('El assessment no esta asociado a una leccion.');
        }

        if (! $lesson->module || $lesson->module->course_id !== $enrollment->course_id) {
            throw new \RuntimeException('La matricula no corresponde al curso del assessment.');
        }

        if (! $assessment->questions()->exists()) {
            throw new \RuntimeException('La evaluacion no tiene preguntas configuradas.');
        }

        $maxAttempts = $assessment->max_attempts;
        if ($maxAttempts !== null && $maxAttempts > 0) {
            $attemptCount = AssessmentAttempt::query()
                ->where('assessment_id', $assessment->id)
                ->where('enrollment_id', $enrollment->id)
                ->where('user_id', $user->id)
                ->count();

            if ($attemptCount >= $maxAttempts) {
                throw new \RuntimeException(
                    "Se ha alcanzado el limite maximo de intentos ({$maxAttempts}) para esta evaluacion."
                );
            }
        }

        return DB::transaction(function () use ($assessment, $enrollment, $lesson, $user) {
            $enrollmentLesson = $this->enrollmentLessons->getOrCreate($enrollment, $lesson);
            $this->enrollmentLessons->touchAccess($enrollmentLesson);

            return AssessmentAttempt::create([
                'assessment_id' => $assessment->id,
                'enrollment_id' => $enrollment->id,
                'lesson_id' => $lesson->id,
                'user_id' => $user->id,
                'status' => 'in_progress',
                'started_at' => now(),
                'passed' => false,
            ]);
        });
    }

    public function submitAttempt(AssessmentAttempt $attempt, array $answers): AssessmentAttempt
    {
        if ($attempt->user_id !== auth()->id()) {
            throw new \RuntimeException('No tienes permiso para enviar esta evaluacion.');
        }

        if ($attempt->status !== 'in_progress') {
            throw new \RuntimeException('Este intento ya ha sido completado.');
        }

        $assessment = $attempt->assessment()->with('questions.questionOptions')->firstOrFail();
        $normalizedAnswers = $this->normalizeAnswers($assessment, $answers);

        DB::transaction(function () use ($attempt, $assessment, $normalizedAnswers) {
            $attempt->responses = $normalizedAnswers;
            $attempt->save();

            $attempt->userAnswers()->delete();

            foreach ($assessment->questions as $question) {
                $answer = $normalizedAnswers[$question->id] ?? null;

                if ($question->isMultipleChoiceMultiple()) {
                    foreach ($answer ?? [] as $optionId) {
                        UserAnswer::create([
                            'user_id' => $attempt->user_id,
                            'question_id' => $question->id,
                            'question_option_id' => $optionId,
                            'assessment_attempt_id' => $attempt->id,
                        ]);
                    }

                    continue;
                }

                if ($question->isOptionBased() && is_numeric($answer)) {
                    UserAnswer::create([
                        'user_id' => $attempt->user_id,
                        'question_id' => $question->id,
                        'question_option_id' => (int) $answer,
                        'assessment_attempt_id' => $attempt->id,
                    ]);
                }
            }
        });

        return $attempt->fresh();
    }

    public function gradeAttempt(AssessmentAttempt $attempt, ?array $answers = null): AssessmentAttempt
    {
        return DB::transaction(function () use ($attempt, $answers) {
            if ($answers !== null) {
                $attempt = $this->submitAttempt($attempt, $answers);
            }

            $assessment = $attempt->assessment()->with('questions.questionOptions', 'lesson.module')->firstOrFail();
            $lesson = $attempt->lesson ?? $assessment->lesson;

            if (! $lesson) {
                throw new \RuntimeException('No se pudo resolver la leccion del intento.');
            }

            $summary = $this->buildAttemptSummaryFromAssessment(
                $assessment,
                $attempt->responses ?? [],
                $attempt
            );

            $attempt->status = 'completed';
            $attempt->completed_at = now();
            $attempt->score = $summary['score'];
            $attempt->passed = $summary['passed'];
            $attempt->passed_at = $summary['passed'] ? now() : null;
            $attempt->feedback = $summary['feedback'];
            $attempt->lesson_id ??= $lesson->id;
            $attempt->save();

            if ($attempt->enrollment) {
                $enrollmentLesson = $this->enrollmentLessons->getOrCreate($attempt->enrollment, $lesson);

                if ($summary['passed']) {
                    $this->enrollmentLessons->markPassed($enrollmentLesson, $attempt);
                } else {
                    $this->enrollmentLessons->markConsumed($enrollmentLesson);
                }
            }

            return $attempt->fresh();
        });
    }

    public function buildAttemptSummary(AssessmentAttempt $attempt): array
    {
        $assessment = $attempt->assessment()->with('questions.questionOptions', 'lesson.module')->firstOrFail();

        return $this->buildAttemptSummaryFromAssessment(
            $assessment,
            $attempt->responses ?? [],
            $attempt
        );
    }

    public function getRemainingAttempts(Assessment $assessment, Enrollment $enrollment, $user): ?int
    {
        if ($assessment->max_attempts === null || $assessment->max_attempts <= 0) {
            return null;
        }

        $usedAttempts = AssessmentAttempt::query()
            ->where('assessment_id', $assessment->id)
            ->where('enrollment_id', $enrollment->id)
            ->where('user_id', $user->id)
            ->count();

        return max(0, $assessment->max_attempts - $usedAttempts);
    }

    public function canStartAttempt(Assessment $assessment, Enrollment $enrollment, $user): array
    {
        if ($user->id !== $enrollment->user_id) {
            return [false, 'El usuario no esta inscrito en esta matricula.'];
        }

        if (! $assessment->active) {
            return [false, 'La evaluacion no esta activa.'];
        }

        if (! $assessment->lesson) {
            return [false, 'El assessment no esta asociado a una leccion.'];
        }

        if (! $assessment->questions()->exists()) {
            return [false, 'La evaluacion no tiene preguntas configuradas.'];
        }

        if (! $assessment->lesson->module || $assessment->lesson->module->course_id !== $enrollment->course_id) {
            return [false, 'La matricula no corresponde al curso del assessment.'];
        }

        if ($assessment->max_attempts !== null && $assessment->max_attempts > 0) {
            $remainingAttempts = $this->getRemainingAttempts($assessment, $enrollment, $user);

            if ($remainingAttempts === 0) {
                return [false, "Se ha alcanzado el limite maximo de intentos ({$assessment->max_attempts}) para esta evaluacion."];
            }
        }

        return [true, null];
    }

    private function normalizeAnswers(Assessment $assessment, array $answers): array
    {
        $normalizedAnswers = [];

        foreach ($assessment->questions as $question) {
            $answer = $answers[$question->id] ?? null;

            if ($question->isMultipleChoiceMultiple()) {
                $optionIds = collect($answer ?? [])
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->map(fn ($value) => (int) $value)
                    ->unique()
                    ->values();

                if ($optionIds->isEmpty()) {
                    continue;
                }

                $validOptionIds = $question->questionOptions
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id);

                if ($optionIds->diff($validOptionIds)->isNotEmpty()) {
                    throw new \RuntimeException('Se detectaron opciones invalidas en la evaluacion.');
                }

                $normalizedAnswers[$question->id] = $optionIds->all();
                continue;
            }

            if ($question->isOptionBased()) {
                if ($answer === null || $answer === '') {
                    continue;
                }

                $optionId = (int) $answer;
                $validOptionIds = $question->questionOptions
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id);

                if (! $validOptionIds->contains($optionId)) {
                    throw new \RuntimeException('Se detectaron opciones invalidas en la evaluacion.');
                }

                $normalizedAnswers[$question->id] = $optionId;
                continue;
            }

            $textAnswer = trim((string) $answer);

            if ($textAnswer !== '') {
                $normalizedAnswers[$question->id] = $textAnswer;
            }
        }

        return $normalizedAnswers;
    }

    private function buildAttemptSummaryFromAssessment(
        Assessment $assessment,
        array $answers,
        ?AssessmentAttempt $attempt = null
    ): array {
        $questions = $assessment->questions;
        $gradableQuestions = $questions->filter(fn (Question $question) => $question->isAutoGradable());

        if ($gradableQuestions->isEmpty()) {
            throw new \RuntimeException('La evaluacion no tiene preguntas calificables automaticamente.');
        }

        $correctAnswers = 0;

        foreach ($gradableQuestions as $question) {
            if ($this->isAnswerCorrect($question, $answers[$question->id] ?? null)) {
                $correctAnswers++;
            }
        }

        $maxScore = (float) ($assessment->max_score ?? 100);
        if ($maxScore <= 0) {
            $maxScore = 100.0;
        }

        $score = round(($correctAnswers / $gradableQuestions->count()) * $maxScore, 2);
        $scorePercentage = round(($score / $maxScore) * 100, 2);
        $passed = $score >= (float) ($assessment->passing_score ?? 60);

        return [
            'score' => $score,
            'max_score' => $maxScore,
            'score_percentage' => $scorePercentage,
            'passed' => $passed,
            'correct_answers' => $correctAnswers,
            'gradable_questions' => $gradableQuestions->count(),
            'total_questions' => $questions->count(),
            'duration' => $attempt?->durationInMinutes(),
            'feedback' => $assessment->show_feedback
                ? $this->generateFeedback($scorePercentage)
                : null,
        ];
    }

    private function isAnswerCorrect(Question $question, mixed $answer): bool
    {
        if ($question->isFreeText()) {
            return false;
        }

        if ($answer === null || $answer === '' || $answer === []) {
            return false;
        }

        if ($question->isMultipleChoiceSingle() || $question->isTrueFalse()) {
            $correctOptionId = $question->questionOptions
                ->firstWhere('is_correct', true)
                ?->id;

            return $correctOptionId !== null && (int) $answer === (int) $correctOptionId;
        }

        if ($question->isMultipleChoiceMultiple()) {
            $correctOptionIds = $question->questionOptions
                ->where('is_correct', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            $selectedOptionIds = collect($answer)
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            return $correctOptionIds->isNotEmpty()
                && $correctOptionIds->all() === $selectedOptionIds->all();
        }

        return false;
    }

    private function generateFeedback(float $scorePercentage): string
    {
        if ($scorePercentage >= 90) {
            return 'Excelente desempeno. Demostraste dominio del tema.';
        }

        if ($scorePercentage >= 80) {
            return 'Buen trabajo. Aun asi vale la pena repasar algunos puntos.';
        }

        if ($scorePercentage >= 70) {
            return 'Vas bien, pero conviene reforzar algunos conceptos clave.';
        }

        return 'Necesitas reforzar el contenido antes de intentarlo de nuevo.';
    }
}
