<?php

namespace App\Models\Quality\Training;

use App\Events\Quality\Training\EnrollmentCompleted;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    /** @use HasFactory<\Database\Factories\Quality\Training\EnrollmentFactory> */
    use HasFactory;

    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

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

    public function assessmentAttempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function enrollmentLessons(): HasMany
    {
        return $this->hasMany(EnrollmentLesson::class);
    }

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'enrollment_lesson')
            ->withPivot([
                'status',
                'started_at',
                'completed_at',
                'last_accessed_at',
                'consumed_at',
                'passed',
                'passed_at',
                'approved_attempt_id',
                'certificate_issued_at',
                'certificate_url',
                'certificate_code',
            ])
            ->withTimestamps();
    }

    /**
     * Alias legacy para el seguimiento por lección.
     */
    public function progress(): HasMany
    {
        return $this->enrollmentLessons();
    }

    public function lessonsCompleted(): HasMany
    {
        return $this->enrollmentLessons()->where(function ($query) {
            $query->where('status', EnrollmentLesson::STATUS_PASSED)
                ->orWhere(function ($subQuery) {
                    $subQuery->where('status', EnrollmentLesson::STATUS_CONSUMED)
                        ->whereHas('lesson', function ($lessonQuery) {
                            $lessonQuery->where('completion_mode', Lesson::COMPLETION_MODE_CONSUMPTION_ONLY);
                        });
                });
        });
    }

    /**
     * Actualiza y guarda el campo progress (0-100) basado en lecciones resueltas del curso.
     */
    public function updateProgress(): void
    {
        $totalLessons = $this->course?->lessons()->count() ?? 0;
        $lessonProgress = $this->enrollmentLessons()
            ->with(['lesson:id,completion_mode', 'approvedAttempt:id,score'])
            ->get();
        $resolvedLessons = $lessonProgress->filter(fn (EnrollmentLesson $lesson) => $lesson->isResolved())->count();
        $hasActivity = $lessonProgress->contains(function (EnrollmentLesson $lesson): bool {
            return $lesson->status !== EnrollmentLesson::STATUS_NOT_STARTED
                || $lesson->started_at !== null
                || $lesson->last_accessed_at !== null
                || $lesson->consumed_at !== null;
        });
        $finalScore = $this->calculateFinalScore($lessonProgress);
        $latestAccessedAt = $lessonProgress
            ->pluck('last_accessed_at')
            ->filter()
            ->sortDesc()
            ->first();
        $startedAt = $lessonProgress
            ->pluck('started_at')
            ->filter()
            ->sort()
            ->first();

        if ($totalLessons === 0) {
            $this->progress = 0;
            $this->status = self::STATUS_NOT_STARTED;
            $this->completed_at = null;
            $this->score_final = null;
            $this->saveQuietly();
            return;
        }

        $percent = intval(round(($resolvedLessons / $totalLessons) * 100));

        $this->progress = $percent;
        $this->score_final = $finalScore;
        $this->last_accessed_at = $latestAccessedAt ?? $this->last_accessed_at;
        $this->started_at ??= $startedAt;

        if ($resolvedLessons >= $totalLessons) {
            $this->markAsCompleted($finalScore);
            return;
        }

        if ($hasActivity) {
            $this->status = self::STATUS_IN_PROGRESS;
            $this->started_at ??= now();
        } else {
            $this->status = self::STATUS_NOT_STARTED;
        }

        $this->completed_at = null;
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
            $this->status = self::STATUS_IN_PROGRESS;
            $this->saveQuietly();
        }
    }

    /**
     * Marcar el curso como completado.
     */
    public function markAsCompleted(?float $finalScore = null)
    {
        $wasCompleted = $this->isCompleted();

        $this->status = self::STATUS_COMPLETED;
        $this->progress = 100;
        $this->started_at ??= now();
        $this->completed_at ??= now();
        $this->score_final = $finalScore ?? $this->calculateFinalScore();

        if ($wasCompleted) {
            $this->saveQuietly();
            return;
        }

        $this->save();

        EnrollmentCompleted::dispatch($this->fresh(), $this->score_final);
    }

    /**
     * Verificar si el curso está completado.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Obtener la última lección accedida.
     */
    public function lastLessonAccessed()
    {
        return $this->enrollmentLessons()
            ->whereNotNull('last_accessed_at')
            ->orderByDesc('last_accessed_at')
            ->first();
    }

    /**
     * Verificar si tiene certificado.
     */
    public function hasCertificate(): bool
    {
        return !empty($this->certificate_url) && $this->certificated_at !== null;
    }

    /**
     * Relación con Certificados
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Obtener el certificado válido más reciente
     */
    public function getLatestCertificate(): ?Certificate
    {
        return $this->certificates()
            ->where('status', 'issued')
            ->orderByDesc('issued_at')
            ->first();
    }

    /**
     * Reiniciar el progreso de la inscripción.
     */
    public function resetProgress()
    {
        $this->assessmentAttempts()->delete();
        $this->enrollmentLessons()->delete();
        $this->progress = 0;
        $this->status = self::STATUS_NOT_STARTED;
        $this->started_at = null;
        $this->completed_at = null;
        $this->last_accessed_at = null;
        $this->certificated_at = null;
        $this->certificate_url = null;
        $this->score_final = null;
        $this->save();
    }

    protected function calculateFinalScore(?EloquentCollection $lessonProgress = null): ?float
    {
        $lessonProgress ??= $this->enrollmentLessons()
            ->with(['lesson:id,completion_mode', 'approvedAttempt:id,score'])
            ->get();

        $scores = $lessonProgress
            ->filter(function (EnrollmentLesson $lesson): bool {
                return $lesson->lesson?->requiresAssessment()
                    && $lesson->status === EnrollmentLesson::STATUS_PASSED
                    && $lesson->approvedAttempt?->score !== null;
            })
            ->map(fn (EnrollmentLesson $lesson) => (float) $lesson->approvedAttempt->score);

        if ($scores->isEmpty()) {
            return null;
        }

        return round($scores->avg(), 2);
    }
}
