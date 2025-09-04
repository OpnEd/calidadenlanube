<?php

namespace App\Services\Quality;

use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Enrollment;
use App\Repositories\Interfaces\CourseInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrainingService
{
    public function __construct(
        protected CourseInterface $courses
    ) {}

    /**
     * Lista los cursos activos disponibles para el usuario.
     */
    public function listAvailableCourses(): Collection
    {
        return $this->courses->findActivecourses();
    }

    /**
     * Inscribe al usuario y prepara el progreso de sus lecciones.
     *
     * @param int $teamId
     * @param int $userId
     * @param int $courseId
     * @return array{ enrollment: Enrollment, course: Course }
     *
     * @throws \Exception si ya está inscrito o no existe el curso
     */
    public function enroll(int $teamId, int $userId, int $courseId): array
    {
        // 1) Verifica si ya existe la inscripción
        $existing = Enrollment::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($existing) {
            throw new \RuntimeException('El usuario ya está inscrito en este curso.');
        }

        // 2) Inicia la transacción
        DB::beginTransaction();

        try {
            // a) Crea la inscripción
            $enrollment = Enrollment::create([
                'team_id'    => $teamId,
                'user_id'    => $userId,
                'course_id'  => $courseId,
                'started_at' => now(),
                'status'     => 'in progress',
            ]);

            // b) Obtiene el curso con lecciones directas (si Course tiene lessons relation)
            //    o módulos y sus lecciones si esa es la estructura
            $course = Course::with('modules.lessons:id,module_id') // Optimización: solo traer IDs necesarios
                ->findOrFail($courseId);

            // c) Prepara los registros de progreso para todas las lecciones del curso
            $lessonIds = $course->modules->pluck('lessons')->flatten()->pluck('id');

            if ($lessonIds->isNotEmpty()) {
                // Prepara los datos para la tabla pivote 'enrollment_lesson'
                $pivotData = $lessonIds->mapWithKeys(fn ($id) => [
                    $id => ['status' => 'not_started']
                ])->all();

                $enrollment->lessons()->attach($pivotData);
            }

            DB::commit();

            // 3) Retorna la inscripción y el curso completo con módulos y lecciones
            return [
                'enrollment' => $enrollment,
                'course'     => $course,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            // Vuelve a lanzar para que el controlador o Livewire lo capture
            throw $e;
        }
    }

    /**
     * Obtiene el progreso del usuario en un curso.
     *
     * @param int $userId
     * @param int $courseId
     * @return array
     */
    public function progress(int $userId, int $courseId): array
    {
        // Obtiene el total de módulos del curso
        $totalModules = DB::table('modules')
            ->where('course_id', $courseId)
            ->count();

        // Obtiene el número de módulos completados por el usuario
        $completedModules = DB::table('module_user')
            ->where('user_id', $userId)
            ->whereIn('module_id', function ($query) use ($courseId) {
                $query->select('id')
                    ->from('modules')
                    ->where('course_id', $courseId);
            })
            ->count();

        // Calcula el porcentaje de progreso
        $progress = $totalModules > 0 ? round(($completedModules / $totalModules) * 100, 2) : 0;

        return [
            'total_modules' => $totalModules,
            'completed_modules' => $completedModules,
            'progress_percent' => $progress,
        ];
    }

    /**
     * Otras operaciones (enroll, progreso, etc.)...
     */
}
