<?php

namespace App\Models\Quality\Training;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Enrollment extends Model
{
    /** @use HasFactory<\Database\Factories\Quality\Training\EnrollmentFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id', // Optional: if the enrollment is associated with a team
        'user_id',
        'course_id',
        'status', // e.g., 'completed', 'in_progress'
        'progress', // Percentage of course completed
        'started_at',
        'completed_at',
        'last_accessed_at',
        'certificated_at', // Timestamp when the certificate was issued
        'certificate_url', // URL to the completion certificate if applicable
        'score_final', // Final score if applicable
    ];

    protected $casts = [
        'status' => 'string',
        'progress' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'certificated_at' => 'datetime',
        'score_final' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessmentAttempts()
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function lessons()
    {
        // pivot enrollment_lesson
        return $this->belongsToMany(Lesson::class, 'enrollment_lesson')
            ->withPivot(['passed', 'passed_at'])
            ->withTimestamps();
    }
    /**
     * Actualiza y guarda el campo progress (0-100) basado en lecciones aprobadas del curso.
     */
    public function updateProgress(): void
    {
        // total de lecciones del curso
        $totalLessons = $this->course->modules()->withCount('lessons')->get()
            ->sum(fn($m) => $m->lessons_count);
        if ($totalLessons === 0) {
            $this->progress = 0;
            $this->saveQuietly();
            return;
        }

        // lecciones aprobadas por este enrollment (pivot passed = true)
        $approved = DB::table('enrollment_lesson')
            ->where('enrollment_id', $this->id)
            ->where('passed', true)
            ->count();

        $percent = intval(round(($approved / $totalLessons) * 100));
        $this->progress = $percent;
        $this->saveQuietly();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function lessonsCompleted()
    {
        return $this->progress()->where('status', 'completed');
    }

    public function percentageCompleted(): float
    {
        $total = $this->course->lessons()->count();
        $done = $this->lessonsCompleted()->count();

        return $total ? round(($done / $total) * 100, 2) : 0.0;
    }

    /**
     * Marcar el curso como iniciado.
     */
    public function markAsStarted()
    {
        if (!$this->started_at) {
            $this->started_at = now();
            $this->status = 'in_progress';
            $this->save();
        }
    }

    /**
     * Marcar el curso como completado.
     */
    public function markAsCompleted()
    {
        $this->completed_at = now();
        $this->status = 'completed';
        $this->save();
    }

    /**
     * Verificar si el curso está completado.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Obtener la última lección accedida.
     */
    public function lastLessonAccessed()
    {
        return $this->progress()->orderByDesc('last_accessed_at')->first();
    }

    /**
     * Verificar si tiene certificado.
     */
    public function hasCertificate(): bool
    {
        return !empty($this->certificate_url) && $this->certificated_at !== null;
    }

    /**
     * Reiniciar el progreso de la inscripción.
     */
    public function resetProgress()
    {
        $this->progress()->delete();
        $this->progress = 0;
        $this->status = 'not_started';
        $this->started_at = null;
        $this->completed_at = null;
        $this->certificated_at = null;
        $this->certificate_url = null;
        $this->score_final = null;
        $this->save();
    }
}
