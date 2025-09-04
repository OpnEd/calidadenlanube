<?php

namespace App\Livewire\Quality\Training;

use App\Models\Quality\Training\AssessmentAttempt;
use App\Models\Quality\Training\Lesson;
use App\Models\Quality\Training\Module;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LessonComponent extends Component
{
    public $userName;
    public $lessonTitle;
    public $lessonObjective;
    public $lessonDescription;
    public $status;
    public $progress;
    public $started_at;
    public $last_accessed_at;
    public $completed_at;
    public $teamId;
    public $record;
    public $content = [];
    public $hasPreviousLesson;
    public $hasNextLesson;
    public $totalLessons;
    public array $lessonStatus = [];
    /** @var Lesson */

    /** @var \Illuminate\Database\Eloquent\Collection */
    public $modules;

    /**
     * Recibe el parámetro lessonId al montar el componente.
     */
    public function mount(Lesson $record)
    {
        //dd($record);
        $this->record = $record->load(['module.course', 'assessment']);
        $this->teamId = Filament::getTenant()->id;

        $module = Module::withCount('lessons')->find($this->record->module_id);
        $this->totalLessons = $module->lessons_count;

        $this->updateNavigationState();
        $this->loadLessonStatus();
    }

    public function render()
    {
        return view('livewire.quality.training.lesson-component');
    }
    /**
     * Detecta el tipo de video y devuelve un arreglo con la info necesaria.
     * Retorna null si no hay video.
     *
     * @return array|null
     */
    /* public function getVideoTypeProperty()
    {
        $url = $this->record->video_url;
        if (! $url) {
            return null;
        }

        // YouTube short link y query param
        if (
            preg_match('/youtu\.be\/([^\?&\/]+)/i', $url, $m) ||
            preg_match('/youtube\.com.*v=([^&\/]+)/i', $url, $m)
        ) {
            $id = $m[1];
            return [
                'type' => 'youtube',
                'embed' => "https://www.youtube.com/embed/{$id}"
            ];
        }

        // Vimeo (simple)
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            $id = $m[1];
            return [
                'type' => 'vimeo',
                'embed' => "https://player.vimeo.com/video/{$id}"
            ];
        }

        // Si es un archivo de video directo (mp4, webm, ogg, mov)
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['mp4', 'webm', 'ogg', 'mov'])) {
            return [
                'type' => 'file',
                'src' => $url
            ];
        }

        // Por defecto: iframe genérico (ej: embed de otras plataformas)
        return [
            'type' => 'iframe',
            'src' => $url
        ];
    } */

    /**
     * Navegar a la lección anterior dentro del mismo módulo (por campo `order`).
     */
    public function previous()
    {
        $previousLesson = Lesson::where('module_id', $this->record->module_id)
            ->where('order', '<', $this->record->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($previousLesson) {
            $this->record = $previousLesson->loadMissing('module');
            $this->updateNavigationState();
        }
    }

    /**
     * Navegar a la siguiente lección dentro del mismo módulo (por campo `order`).
     */
    public function next()
    {
        $nextLesson = Lesson::where('module_id', $this->record->module_id)
            ->where('order', '>', $this->record->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($nextLesson) {
            $this->record = $nextLesson->loadMissing('module');
            $this->updateNavigationState();
        }
    }

    private function updateNavigationState(): void
    {
        $this->hasPreviousLesson = Lesson::where('module_id', $this->record->module_id)->where('order', '<', $this->record->order)->exists();
        $this->hasNextLesson = Lesson::where('module_id', $this->record->module_id)->where('order', '>', $this->record->order)->exists();
    }

    /**
     * Carga el estado de la lección actual para el usuario autenticado.
     */
    protected function loadLessonStatus(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->lessonStatus = ['text' => 'No disponible', 'color' => 'gray'];
            return;
        }

        // Estado por defecto
        $status = ['text' => 'No cursada', 'color' => 'gray'];

        // Buscar la inscripción del usuario al curso al que pertenece esta lección
        $enrollment = $user->enrollments()
            ->where('course_id', $this->record->module->course_id)
            ->first();

        if ($enrollment) {
            // Verificar el progreso en la tabla pivote
            $progress = DB::table('enrollment_lesson')
                ->where('enrollment_id', $enrollment->id)
                ->where('lesson_id', $this->record->id)
                ->first();

            if ($progress) {
                if ($progress->status === 'in_progress') {
                    $status = ['text' => 'En progreso', 'color' => 'warning'];
                } elseif ($progress->status === 'completed') {
                    // Si está completada, verificar el resultado de la evaluación si existe
                    $assessment = $this->record->assessment;
                    if ($assessment) {
                        $latestAttempt = AssessmentAttempt::query()
                            ->where('user_id', $user->id)
                            ->where('assessment_id', $assessment->id)
                            ->where('status', 'completed')
                            ->latest()
                            ->first();

                        if ($latestAttempt) {
                            $status = $latestAttempt->passed
                                ? ['text' => 'Aprobada', 'color' => 'success']
                                : ['text' => 'Reprobada', 'color' => 'danger'];
                        } else {
                            $status = ['text' => 'Completada', 'color' => 'primary'];
                        }
                    } else {
                        $status = ['text' => 'Completada', 'color' => 'primary'];
                    }
                }
            }
        }

        $this->lessonStatus = $status;
    }
}
