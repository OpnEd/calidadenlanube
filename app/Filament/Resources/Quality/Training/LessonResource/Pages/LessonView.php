<?php

namespace App\Filament\Resources\Quality\Training\LessonResource\Pages;

use App\Filament\Resources\Quality\Training\LessonResource;
use App\Models\Quality\Training\AssessmentAttempt;
use App\Models\Quality\Training\Question;
use App\Models\Quality\Training\UserAnswer;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Database\Eloquent\Model;

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

        // Verificar que el usuario tiene enrollment para el curso
        $user = auth()->user();
        $course = $this->record->module->course;
        $enrollment = $user->enrollments()->where('course_id', $course->id)->first();

        if ($enrollment) {
            $assessment = $this->record->assessment;

            if ($assessment) {
                $actions[] = Action::make('realizar-assessment')
                    ->label('Realizar Assessment')
                    ->form(function () use ($assessment) {
                        $formSchema = [];
                        $questions = $assessment->questions()->with('question_options')->get();

                        foreach ($questions as $question) {
                            $options = $question->question_options->pluck('option_text', 'id')->toArray();

                            // Asumimos tipos de pregunta como 'multiple_choice_single', 'multiple_choice_multiple', 'open_text'
                            // Debes ajustar esto a los tipos que realmente uses.
                            switch ($question->type) {
                                case 'multiple_choice_single':
                                    $formSchema[] = Radio::make('answers.' . $question->id)
                                        ->label($question->question_text)
                                        ->options($options)
                                        ->required();
                                    break;
                                case 'multiple_choice_multiple':
                                    $formSchema[] = CheckboxList::make('answers.' . $question->id)
                                        ->label($question->question_text)
                                        ->options($options)
                                        ->required();
                                    break;
                                case 'free_text':
                                    $formSchema[] = Textarea::make('answers.' . $question->id)
                                        ->label($question->question_text)
                                        ->required();
                                    break;
                                case 'true_false':
                                    // Usamos Radio en lugar de Checkbox para asegurar una única respuesta
                                    $formSchema[] = Radio::make('answers.' . $question->id)
                                        ->label($question->question_text)
                                        ->options($options)
                                        ->required();
                                    break;
                            }
                        }
                        return $formSchema;
                    })
                    ->action(function (array $data) use ($user, $assessment) {
                        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $user, $assessment) {
                            $attempt = AssessmentAttempt::create([
                                'assessment_id' => $assessment->id,
                                'user_id' => $user->id,
                                'status' => 'in_progress',
                                'started_at' => now(),
                                'responses' => $data['answers'],
                            ]);

                            $score = 0;
                            $questions = $assessment->questions()->with('question_options')->get();
                            $totalQuestions = $questions->count();
                            $pointsPerQuestion = $totalQuestions > 0 ? $assessment->max_score / $totalQuestions : 0;

                            foreach ($data['answers'] as $questionId => $userAnswer) {
                                $question = $questions->find($questionId);
                                if (!$question) continue;

                                $isCorrect = false;

                                if (in_array($question->type, ['multiple_choice_single', 'true_false'])) { // Correctness for single choice
                                    $correctOption = $question->question_options->where('is_correct', true)->first();
                                    if ($correctOption && $correctOption->id == $userAnswer) {
                                        $isCorrect = true;
                                    }
                                } elseif ($question->type === 'multiple_choice_multiple') { // Correctness for multiple choice
                                    // Get IDs of all correct options for this question
                                    $correctOptionIds = $question->question_options->where('is_correct', true)->pluck('id')->sort()->values();
                                    // Get IDs selected by the user
                                    $userAnswerIds = collect($userAnswer)->map(fn ($id) => (int)$id)->sort()->values();

                                    // The answer is correct if the user's selection exactly matches the correct options
                                    if ($correctOptionIds->isNotEmpty() && $correctOptionIds->all() === $userAnswerIds->all()) {
                                        $isCorrect = true;
                                    }
                                }

                                if ($isCorrect) {
                                    $score += $pointsPerQuestion;
                                }

                                // --- Guardar las respuestas del usuario ---
                                // Solo se guardan respuestas para preguntas con opciones.
                                // Las respuestas de texto libre ya están en la columna 'responses' del intento.

                                if ($question->type === 'multiple_choice_multiple') {
                                    // Para respuestas múltiples, iterar sobre cada opción seleccionada
                                    if (is_array($userAnswer)) {
                                        foreach ($userAnswer as $optionId) {
                                            UserAnswer::create(['user_id' => $user->id, 'question_id' => $questionId, 'question_option_id' => $optionId, 'assessment_attempt_id' => $attempt->id]);
                                        }
                                    }
                                } elseif (in_array($question->type, ['multiple_choice_single', 'true_false'])) {
                                    // Para respuestas únicas, crear un solo registro
                                    UserAnswer::create(['user_id' => $user->id, 'question_id' => $questionId, 'question_option_id' => $userAnswer, 'assessment_attempt_id' => $attempt->id]);
                                }
                            }
                            
                            
                            $passed = $score >= $assessment->passing_score;

                            $attempt->update([
                                'score' => round($score, 2),
                                'status' => 'completed',
                                'completed_at' => now(),
                                'passed' => $passed,
                            ]);

                            Notification::make()
                                ->title($passed ? '¡Evaluación Aprobada!' : 'Evaluación no aprobada')
                                ->body("Tu puntaje fue: " . round($score, 2) . "/{$assessment->max_score}. El mínimo para aprobar es {$assessment->passing_score}.")
                                ->{$passed ? 'success' : 'danger'}()
                                ->send();
                        });
                    })
                    ->modalHeading('Evaluación: ' . $assessment->title)
                    ->modalSubmitActionLabel('Enviar Respuestas');
            } else {
                $actions[] = Action::make('no-assessment')
                    ->label('No hay assessment para esta lección')
                    ->disabled();
            }
        } else {
            $actions[] = Action::make('no-enrolled')
                ->label('Debe inscribirse para realizar el assessment')
                ->disabled();
        }

        return $actions;
    }

    // He renombrado `headerActions` a `getHeaderActions` para seguir la convención de Filament 3.
    // Si estás en una versión anterior, puedes mantener el nombre original.
    /* public function headerActions(): array
    {
        return $this->getHeaderActions();
    } */
}
