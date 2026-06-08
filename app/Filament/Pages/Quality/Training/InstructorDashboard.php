<?php

namespace App\Filament\Pages\Quality\Training;

use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Enrollment;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class InstructorDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Universidad';
    protected static ?string $navigationLabel = 'Dashboard del Instructor';
    protected static string $view = 'filament.pages.quality.training.instructor-dashboard';
    protected static ?int $navigationSort = 15;

    public static function canAccess(): bool
    {
        return auth()->user()?->isInstructor() ?? false;
    }

    public function getTitle(): string
    {
        return 'Dashboard del Instructor';
    }

    /**
     * Get all courses taught by the authenticated instructor
     */
    public function getCourses(): Collection
    {
        $instructor = auth()->user();
        $tenant = Filament::getTenant();

        return Course::query()
            ->where('instructor_id', $instructor->id)
            ->where('team_id', $tenant?->id)
            ->with(['modules', 'enrollments'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get enrollments for instructor's courses
     */
    public function getEnrollments()
    {
        $instructor = auth()->user();
        $tenant = Filament::getTenant();

        return Enrollment::query()
            ->whereIn(
                'course_id',
                Course::query()
                    ->where('instructor_id', $instructor->id)
                    ->where('team_id', $tenant?->id)
                    ->pluck('id')
            )
            ->with([
                'user:id,name,email',
                'course:id,title',
                'certificates' => function ($query) {
                    $query->latest('issued_at')->limit(1);
                }
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get statistics for instructor dashboard
     */
    public function getStatistics(): array
    {
        $courses = $this->getCourses();
        $enrollments = $this->getEnrollments();

        $completedEnrollments = $enrollments->where('status', Enrollment::STATUS_COMPLETED);
        $inProgressEnrollments = $enrollments->where('status', Enrollment::STATUS_IN_PROGRESS);

        return [
            'total_courses' => $courses->count(),
            'active_courses' => $courses->where('active', true)->count(),
            'total_students' => $enrollments->count(),
            'students_in_progress' => $inProgressEnrollments->count(),
            'students_completed' => $completedEnrollments->count(),
            'completion_rate' => $enrollments->count() > 0
                ? round(($completedEnrollments->count() / $enrollments->count()) * 100, 2)
                : 0,
            'average_progress' => $enrollments->count() > 0 ? $enrollments->avg('progress') : 0,
            'total_modules' => $courses->sum(fn($course) => $course->modules->count()),
            'certificates_issued' => $enrollments->sum(fn($enrollment) => $enrollment->certificates->count()),
        ];
    }

    /**
     * Get course statistics by course
     */
    public function getCourseStats(): array
    {
        $courses = $this->getCourses();

        return $courses->map(function ($course) {
            $enrollments = $course->enrollments;
            $completed = $enrollments->where('status', Enrollment::STATUS_COMPLETED)->count();

            return [
                'id' => $course->id,
                'title' => $course->title,
                'total_enrollments' => $enrollments->count(),
                'completed_enrollments' => $completed,
                'completion_rate' => $enrollments->count() > 0
                    ? round(($completed / $enrollments->count()) * 100, 2)
                    : 0,
                'average_progress' => $enrollments->count() > 0 ? $enrollments->avg('progress') : 0,
                'modules_count' => $course->modules->count(),
                'created_at' => $course->created_at,
                'active' => $course->active,
            ];
        })->toArray();
    }

    /**
     * Get recent enrollments (last 10)
     */
    public function getRecentEnrollments()
    {
        return $this->getEnrollments()
            ->take(10)
            ->values();
    }
}
