<?php

namespace App\Helpers\Training;

use App\Filament\Pages\Quality\Training\StudentDashboard;
use App\Filament\Resources\Quality\Training\EnrollmentResource;
use App\Models\Quality\Training\Enrollment;
use App\Models\Quality\Training\Lesson;
use App\Models\Quality\Training\Module;

class BreadcrumbHelper
{
    /**
     * @return array<array{label: string, url: ?string}>
     */
    public static function getTrainingBreadcrumbs(
        Enrollment $enrollment,
        ?Module $module = null,
        ?Lesson $lesson = null
    ): array {
        $breadcrumbs = [
            [
                'label' => 'Inicio',
                'url' => StudentDashboard::getUrl(),
            ],
            [
                'label' => 'Mis cursos',
                'url' => EnrollmentResource::getUrl('index'),
            ],
            [
                'label' => $enrollment->course->title,
                'url' => EnrollmentResource::getUrl('view', ['record' => $enrollment->getKey()]),
            ],
        ];

        if ($module) {
            $breadcrumbs[] = [
                'label' => $module->title,
                'url' => null,
            ];
        }

        if ($lesson) {
            $breadcrumbs[] = [
                'label' => $lesson->title,
                'url' => null,
            ];
        }

        return $breadcrumbs;
    }

    public static function getBreadcrumbPath(
        Enrollment $enrollment,
        ?Module $module = null,
        ?Lesson $lesson = null
    ): string {
        $parts = [$enrollment->course->title];

        if ($module) {
            $parts[] = $module->title;
        }

        if ($lesson) {
            $parts[] = $lesson->title;
        }

        return implode(' > ', $parts);
    }
}
