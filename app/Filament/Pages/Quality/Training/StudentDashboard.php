<?php

namespace App\Filament\Pages\Quality\Training;

use App\Models\Quality\Training\Enrollment;
use Filament\Facades\Filament;
use Filament\Pages\Page;

class StudentDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Universidad';
    protected static ?string $navigationLabel = 'Mi Dashboard';
    protected static string $view = 'filament.pages.quality.training.student-dashboard';
    protected static ?int $navigationSort = 4;

    public function getTitle(): string
    {
        return 'Mi Aprendizaje';
    }

    public function getEnrollments()
    {
        $user = auth()->user();
        $tenant = Filament::getTenant();

        return Enrollment::query()
            ->where('user_id', $user->id)
            ->where('team_id', $tenant?->id)
            ->with(['course:id,title,description,image,instructor_id', 'course.instructor:id,name'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getStatistics(): array
    {
        $user = auth()->user();
        $tenant = Filament::getTenant();

        $enrollments = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('team_id', $tenant?->id)
            ->get();

        return [
            'total_enrollments' => $enrollments->count(),
            'in_progress' => $enrollments->where('status', Enrollment::STATUS_IN_PROGRESS)->count(),
            'completed' => $enrollments->where('status', Enrollment::STATUS_COMPLETED)->count(),
            'average_progress' => $enrollments->avg('progress'),
        ];
    }
}
