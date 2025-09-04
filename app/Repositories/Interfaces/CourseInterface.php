<?php

namespace App\Repositories\Interfaces;

use App\Models\Quality\Training\Course;
use Illuminate\Support\Collection;

interface CourseInterface
{
    /**
     * Get all courses.
     *
     * @return Collection<Course>
     */
    public function all(): Collection;

    /**
     * Find a course by its ID.
     *
     * @param int $id
     * @return Course|null
     */
    public function find(int $id): ?Course;

    /**
     * Find courses by category.
     *
     * @param string $category
     * @return Collection<Course>
     */
    public function findByCategory(string $category): Collection;

    /**
     * Find all active courses.
     * 
     * @return Collection<Course>
     */
    public function findActivecourses(): Collection;

    /**
     * Find courses by instructor ID.
     * 
     * @param int $instructorId
     * @return Collection<Course>
     */
    public function findCoursesByInstructor(int $instructorId): Collection;
}
