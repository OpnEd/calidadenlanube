<?php
namespace App\Repositories;

use App\Models\Quality\Training\Course;
use App\Repositories\Interfaces\CourseInterface;
use Illuminate\Support\Collection;

class CourseRepository implements CourseInterface
{
    public function all(): Collection
    {
        return Course::all();
    }

    public function allActive(): Collection
    {
        // El scope global de tenant ya restringe al tenant actual
        return Course::query()
            ->where('active', true)
            ->orderBy('title')
            ->get();
    }

    public function find(int $id): ?Course
    {
        return Course::query()->find($id);
    }

    public function findByCategory($category): Collection
    {
        return Course::where('category', $category)->get();
    }

    public function findActivecourses(): Collection
    {
        return Course::where('active', true)->get();
    }

    public function findCoursesByInstructor(int $instructorId): Collection
    {
        return Course::where('instructor_id', $instructorId)->get();
    }
}
