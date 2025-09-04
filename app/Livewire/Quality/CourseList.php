<?php

namespace App\Livewire\Quality;

use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Enrollment;
use App\Services\Quality\TrainingService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CourseList extends Component
{
    /** @var Collection */
    public $courses;
    public ?int  $selectedCourseId = null;
    public $userEnrollments;
    public $teamId;

    protected $training;

    public function mount(TrainingService $training)
    {
        $this->teamId = Filament::getTenant()->id;
        $this->training = $training;
        // Inyectamos el servicio y obtenemos los cursos
        $this->courses = $this->training->listAvailableCourses();
        $this->userEnrollments = Enrollment::where('user_id', Auth::user()->id)->where('team_id', $this->teamId)->get()->keyBy('course_id');
        //dd($this->courses);
    }

    /**
     * Ejecuta la inscripción llamando al servicio.
     */
    public function confirmEnrollment(int $courseId)
    {
        $training = app(TrainingService::class);
        $this->selectedCourseId = $courseId;

        if (! $this->selectedCourseId) {

            Notification::make()
                ->title('Error')
                ->body('No se ha seleccionado ningún curso.')
                ->danger()
                ->send();

            return;
        }

        $teamId   = Filament::getTenant()->id;
        $userId   = Auth::user()->id;
        $course = Course::findOrFail($this->selectedCourseId);

        try {

            $enrolled = $training->enroll($teamId, $userId, $courseId);

            if (! $enrolled) {
                Notification::make()
                    ->title('Ya inscrito')
                    ->body("Ya estás inscrito en el curso: {$course->title}")
                    ->warning()
                    ->send();
            } else {
                Notification::make()
                    ->title('Inscripción exitosa')
                    ->body("Te has inscrito en el curso: {$course->title}")
                    ->success()
                    ->send();
            }

            // Cierra el modal después de la acción, sin importar el resultado
            $this->dispatch('close-modal', id: 'enrollUser');
//dd($teamId, $enrolled['enrollment']->id);
            // Rediriges o emites datos para mostrar módulos:
            return redirect()->route('filament.admin.resources.quality.training.enrollments.view', [
                $teamId,
                $enrolled['enrollment']->id,
            ]);

        } catch (\Exception $e) {

            Notification::make()
                ->title('Error al inscribirse')
                ->body("No se pudo completar la inscripción: {$e->getMessage()}")
                ->danger()
                ->send();

            $this->dispatch('close-modal', id: 'enrollUser');

        }
    }

    public function goToEnrollment(int $enrollmentId)
    {
        return redirect()->route('filament.admin.resources.quality.training.enrollments.view', [
            'tenant' => $this->teamId,
            'record' => $enrollmentId,
        ]);
    }


    public function render()
    {
        return view('livewire.quality.course-list');
    }
}
