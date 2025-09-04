<?php
namespace App\Services;

use App\Models\Quality\Training\Assessment;
use App\Models\Quality\Training\AssessmentAttempt;
use App\Models\Quality\Training\Enrollment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssessmentService
{
    /**
     * Inicia un intento (crea registro) para un assessment y enrollment.
     */
    public function startAttempt(Assessment $assessment, Enrollment $enrollment, $user)
    {
        // validar que el enrollment corresponde al course de la lesson
        if ($assessment->lesson->module->course_id !== $enrollment->course_id) {
            throw new \Exception("Enrollment no corresponde al curso del assessment.");
        }

        $attempt = AssessmentAttempt::create([
            'assessment_id' => $assessment->id,
            'enrollment_id' => $enrollment->id,
            'user_id' => $user->id,
            'answers' => null,
            'score' => null,
            'passed' => false,
        ]);

        return $attempt;
    }

    /**
     * Corrige un intento: compara respuestas con las correctas, calcula score y marca passed si aplica.
     * Answers format expected: ['q0' => 'option_index', ...] o similar, depende de tu JSON.
     */
    public function gradeAttempt(AssessmentAttempt $attempt, array $answers): AssessmentAttempt
    {
        $assessment = $attempt->assessment;
        $questions = $assessment->questions ?? [];

        $total = count($questions);
        $correctCount = 0;

        foreach ($questions as $index => $q) {
            // define el identificador de la pregunta en el payload
            $key = "q{$index}";
            $correct = $q['correct'] ?? null; // asumimos que 'correct' guarda el índice o valor
            $given = $answers[$key] ?? null;

            if ($given !== null) {
                // comparación flexible: puede ser índice o valor
                if ($given == $correct) {
                    $correctCount++;
                }
            }
        }

        $score = $total > 0 ? intval(round(($correctCount / $total) * 100)) : 0;
        $passed = $score >= $assessment->pass_percentage;

        $attempt->answers = $answers;
        $attempt->score = $score;
        $attempt->passed = $passed;
        if ($passed && !$attempt->passed_at) {
            $attempt->passed_at = Carbon::now();
        }
        $attempt->save();

        if ($passed) {
            $this->markLessonPassedForEnrollment($assessment->lesson->id, $attempt->enrollment);
        }

        // actualizar progreso del enrollment
        $attempt->enrollment->updateProgress();

        return $attempt->fresh();
    }

    /**
     * Marca la lección como pasada en el pivot enrollment_lesson.
     */
    protected function markLessonPassedForEnrollment(int $lessonId, Enrollment $enrollment)
    {
        $now = Carbon::now();

        DB::table('enrollment_lesson')->updateOrInsert(
            ['enrollment_id' => $enrollment->id, 'lesson_id' => $lessonId],
            ['passed' => true, 'passed_at' => $now, 'updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
        );
    }
}

