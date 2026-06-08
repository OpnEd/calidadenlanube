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
        protected CourseInterface $courses,
        protected EnrollmentLessonService $enrollmentLessons
    ) {}

    public function listAvailableCourses(int $teamId): Collection
    {
        return $this->courses->findActivecourses($teamId);
    }

    /**
     * @return array{ enrollment: Enrollment, course: Course }
     */
    public function enroll(int $teamId, int $userId, int $courseId): array
    {
        $existing = Enrollment::query()
            ->where('team_id', $teamId)
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($existing) {
            throw new \RuntimeException('El usuario ya está inscrito en este curso.');
        }

        return DB::transaction(function () use ($teamId, $userId, $courseId): array {
            $course = Course::query()
                ->discoverableToTeam($teamId)
                ->active()
                ->with('modules.lessons:id,module_id')
                ->findOrFail($courseId);

            $enrollment = Enrollment::create([
                'team_id' => $teamId,
                'user_id' => $userId,
                'course_id' => $courseId,
                'started_at' => now(),
                'status' => Enrollment::STATUS_IN_PROGRESS,
            ]);

            $this->enrollmentLessons->initializeForEnrollment($enrollment, $course);

            return [
                'enrollment' => $enrollment,
                'course' => $course,
            ];
        });
    }

    public function progress(int $userId, int $courseId): array
    {
        $course = Course::query()->find($courseId);
        $enrollment = Enrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        $totalLessons = $course?->lessons()->count() ?? 0;
        $completedLessons = $enrollment?->lessonsCompleted()->count() ?? 0;
        $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

        return [
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'progress_percent' => $progress,
        ];
    }
}
