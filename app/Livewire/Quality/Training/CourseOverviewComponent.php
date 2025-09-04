<?php

namespace App\Livewire\Quality\Training;

use App\Models\Quality\Training\AssessmentAttempt;
use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Enrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CourseOverviewComponent extends Component
{
    public Course $course;
    public Enrollment $record;
    public $team;
    public array $lessonStatuses = [];

    /**
     * Recibe el registro de Enrollment al montar el componente.
     */
    public function mount(Enrollment $record)
    {
        $this->record = $record;
        // Eager load para optimizar consultas para la vista y el cálculo de estados.
        $this->course = $record->course->load('modules.lessons.assessment:id,lesson_id');
        $this->team = filament()->getTenant();
        $this->loadLessonStatuses();
    }

    /**
     * Calcula el estado de cada lección para el usuario actual.
     * Este método está optimizado para reducir las consultas a la base de datos.
     */
    protected function loadLessonStatuses(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        // La inscripción ya está disponible en $this->record
        $enrollment = $this->record;

        // 1. Obtener todas las lecciones del curso para unificar consultas
        $lessons = $this->course->modules->pluck('lessons')->flatten();
        $lessonIds = $lessons->pluck('id');

        if ($lessonIds->isEmpty()) {
            return;
        }

        // 2. Obtener el progreso de todas las lecciones en una sola consulta
        $progressData = DB::table('enrollment_lesson')
            ->where('enrollment_id', $enrollment->id)
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->keyBy('lesson_id');

        // 3. Obtener los IDs de las evaluaciones de las lecciones completadas
        $completedLessonIds = $progressData->where('status', 'completed')->pluck('lesson_id');
        $assessmentIds = $lessons->whereIn('id', $completedLessonIds)->pluck('assessment.id')->filter()->unique();

        // 4. Obtener el último intento para cada evaluación completada en una sola consulta
        $latestAttempts = [];
        if ($assessmentIds->isNotEmpty()) {
            $latestAttempts = AssessmentAttempt::query()
                ->where('user_id', $user->id)
                ->whereIn('assessment_id', $assessmentIds)
                ->where('status', 'completed')
                // Subconsulta para obtener solo el ID del intento más reciente por evaluación
                ->whereIn('id', function ($query) {
                    $query->select(DB::raw('MAX(id)'))
                          ->from('assessment_attempts')
                          ->where('status', 'completed')
                          ->groupBy('assessment_id');
                })
                ->get()
                ->keyBy('assessment_id');
        }

        // 5. Construir el array final de estados para la vista
        foreach ($lessons as $lesson) {
            $status = ['text' => 'No cursada', 'color' => 'gray']; // Estado por defecto

            if ($progress = $progressData->get($lesson->id)) {
                if ($progress->status === 'in_progress') {
                    $status = ['text' => 'En progreso', 'color' => 'warning'];
                } elseif ($progress->status === 'completed') {
                    $assessment = $lesson->assessment;
                    if ($assessment && ($attempt = $latestAttempts->get($assessment->id))) {
                        $status = $attempt->passed
                            ? ['text' => 'Aprobada', 'color' => 'success']
                            : ['text' => 'Reprobada', 'color' => 'danger'];
                    } else {
                        // Si no hay evaluación o intento, se marca como 'Completada'
                        $status = ['text' => 'Completada', 'color' => 'primary'];
                    }
                }
            }
            $this->lessonStatuses[$lesson->id] = $status;
        }
    }

    public function render()
    {
        return view('livewire.quality.training.course-overview-component');
    }
}
