<?php

namespace App\Livewire\Quality\Training;

use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Module;
use Livewire\Component;

class ModuleList extends Component
{
    public $course;
    /** @var int */
    public $courseId;

    /** @var \Illuminate\Database\Eloquent\Collection */
    public $modules;

    /**
     * Recibe el parámetro courseId al montar el componente.
     */
    public function mount(int $courseId)
    {
        dd($courseId);
        $this->course = Course::where('id', $courseId)->first();
        $this->courseId = $courseId;

        // Carga todos los módulos de este curso junto con sus lecciones
        $this->modules = Module::where('course_id', $this->courseId)
            ->with('lessons')
            ->orderBy('id')
            ->get();

    }




    public function render()
    {
        return view('livewire.quality.training.module-list');
    }
}
