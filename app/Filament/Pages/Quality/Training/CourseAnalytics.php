<?php

namespace App\Filament\Pages\Quality\Training;

use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Enrollment;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CourseAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'Universidad';
    protected static ?string $navigationLabel = 'Analítica de Cursos';
    protected static string $view = 'filament.pages.quality.training.course-analytics';
    protected static ?int $navigationSort = 25;

    public static function canAccess(): bool
    {
        return auth()->user()?->isInstructor() ?? false;
    }

    public function getTitle(): string
    {
        return 'Analítica de Cursos';
    }

    /**
     * Get overall platform statistics
     */
    public function getPlatformStats(): array
    {
        $tenant = Filament::getTenant();

        $courses = Course::query()
            ->where('team_id', $tenant?->id)
            ->count();

        $enrollments = Enrollment::query()
            ->where('team_id', $tenant?->id)
            ->count();

        $completedEnrollments = Enrollment::query()
            ->where('team_id', $tenant?->id)
            ->where('status', Enrollment::STATUS_COMPLETED)
            ->count();

        $averageProgress = Enrollment::query()
            ->where('team_id', $tenant?->id)
            ->avg('progress') ?? 0;

        return [
            'total_courses' => $courses,
            'total_enrollments' => $enrollments,
            'completed_enrollments' => $completedEnrollments,
            'completion_rate' => $enrollments > 0 ? round(($completedEnrollments / $enrollments) * 100, 2) : 0,
            'average_progress' => round($averageProgress, 2),
        ];
    }

    /**
     * Get enrollment trend data (monthly breakdown for the last 6 months)
     */
    public function getEnrollmentTrend(): array
    {
        $tenant = Filament::getTenant();

        $months = collect(range(0, 5))
            ->map(fn ($index) => now()->subMonths($index)->startOfMonth())
            ->reverse()
            ->toArray();

        $data = [];

        foreach ($months as $month) {
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $enrollmentCount = Enrollment::query()
                ->where('team_id', $tenant?->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            $completionCount = Enrollment::query()
                ->where('team_id', $tenant?->id)
                ->where('status', Enrollment::STATUS_COMPLETED)
                ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
                ->count();

            $data[] = [
                'month' => $month->format('M Y'),
                'enrollments' => $enrollmentCount,
                'completions' => $completionCount,
            ];
        }

        return $data;
    }

    /**
     * Get course performance metrics
     */
    public function getCoursePerformance(): array
    {
        $tenant = Filament::getTenant();

        $courses = Course::query()
            ->where('team_id', $tenant?->id)
            ->with(['enrollments'])
            ->get();

        return $courses->map(function ($course) {
            $enrollments = $course->enrollments;
            $completed = $enrollments->where('status', Enrollment::STATUS_COMPLETED)->count();
            $inProgress = $enrollments->where('status', Enrollment::STATUS_IN_PROGRESS)->count();
            $notStarted = $enrollments->where('status', Enrollment::STATUS_NOT_STARTED)->count();

            return [
                'id' => $course->id,
                'title' => $course->title,
                'instructor' => $course->instructor->name,
                'total_enrollments' => $enrollments->count(),
                'completed' => $completed,
                'in_progress' => $inProgress,
                'not_started' => $notStarted,
                'completion_rate' => $enrollments->count() > 0 
                    ? round(($completed / $enrollments->count()) * 100, 2)
                    : 0,
                'average_progress' => $enrollments->count() > 0 ? $enrollments->avg('progress') : 0,
                'average_score' => $enrollments->count() > 0 
                    ? round($enrollments->avg('score_final') ?? 0, 2)
                    : 0,
            ];
        })->toArray();
    }

    /**
     * Get student progress distribution
     */
    public function getProgressDistribution(): array
    {
        $tenant = Filament::getTenant();

        $ranges = [
            ['label' => '0-20%', 'min' => 0, 'max' => 20],
            ['label' => '21-40%', 'min' => 21, 'max' => 40],
            ['label' => '41-60%', 'min' => 41, 'max' => 60],
            ['label' => '61-80%', 'min' => 61, 'max' => 80],
            ['label' => '81-100%', 'min' => 81, 'max' => 100],
        ];

        $distribution = [];

        foreach ($ranges as $range) {
            $count = Enrollment::query()
                ->where('team_id', $tenant?->id)
                ->whereBetween('progress', [$range['min'], $range['max']])
                ->count();

            $distribution[] = [
                'label' => $range['label'],
                'count' => $count,
            ];
        }

        return $distribution;
    }

    /**
     * Get completion timeline (day by day for the last 30 days)
     */
    public function getCompletionTimeline(): array
    {
        $tenant = Filament::getTenant();

        $days = collect(range(0, 29))
            ->map(fn ($index) => now()->subDays($index)->startOfDay())
            ->reverse()
            ->toArray();

        $timeline = [];

        foreach ($days as $day) {
            $endOfDay = $day->copy()->endOfDay();

            $completionCount = Enrollment::query()
                ->where('team_id', $tenant?->id)
                ->where('status', Enrollment::STATUS_COMPLETED)
                ->whereBetween('completed_at', [$day, $endOfDay])
                ->count();

            $timeline[] = [
                'date' => $day->format('d/m'),
                'completions' => $completionCount,
            ];
        }

        return $timeline;
    }

    /**
     * Get top courses by enrollment
     */
    public function getTopCoursesbyEnrollment(): array
    {
        $tenant = Filament::getTenant();

        return Course::query()
            ->where('team_id', $tenant?->id)
            ->with(['enrollments'])
            ->get()
            ->map(fn ($course) => [
                'title' => $course->title,
                'enrollment_count' => $course->enrollments->count(),
            ])
            ->sortByDesc('enrollment_count')
            ->take(5)
            ->values()
            ->toArray();
    }

    /**
     * Get top courses by completion rate
     */
    public function getTopCoursesByCompletion(): array
    {
        $tenant = Filament::getTenant();

        return Course::query()
            ->where('team_id', $tenant?->id)
            ->with(['enrollments'])
            ->get()
            ->filter(fn ($course) => $course->enrollments->count() > 0)
            ->map(function ($course) {
                $enrollments = $course->enrollments;
                $completed = $enrollments->where('status', Enrollment::STATUS_COMPLETED)->count();

                return [
                    'title' => $course->title,
                    'completion_rate' => round(($completed / $enrollments->count()) * 100, 2),
                    'enrollments' => $enrollments->count(),
                ];
            })
            ->sortByDesc('completion_rate')
            ->take(5)
            ->values()
            ->toArray();
    }

    /**
     * Get student engagement metrics
     */
    public function getEngagementMetrics(): array
    {
        $tenant = Filament::getTenant();

        $sevenDaysAgo = now()->subDays(7);
        $thirtyDaysAgo = now()->subDays(30);

        $activeLastWeek = Enrollment::query()
            ->where('team_id', $tenant?->id)
            ->where('last_accessed_at', '>=', $sevenDaysAgo)
            ->count();

        $activeLastMonth = Enrollment::query()
            ->where('team_id', $tenant?->id)
            ->where('last_accessed_at', '>=', $thirtyDaysAgo)
            ->count();

        $totalEnrollments = Enrollment::query()
            ->where('team_id', $tenant?->id)
            ->count();

        return [
            'active_last_week' => $activeLastWeek,
            'active_last_month' => $activeLastMonth,
            'engagement_rate_week' => $totalEnrollments > 0 
                ? round(($activeLastWeek / $totalEnrollments) * 100, 2)
                : 0,
            'engagement_rate_month' => $totalEnrollments > 0 
                ? round(($activeLastMonth / $totalEnrollments) * 100, 2)
                : 0,
            'inactive_count' => $totalEnrollments - $activeLastMonth,
        ];
    }
}
