<?php

namespace App\Models\Quality\Training;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    /** @use HasFactory<\Database\Factories\Quality\Training\AssessmentFactory> */
    use HasFactory;

    protected $fillable = [
        'title',            // Título de la evaluación
        'description',      // Instrucciones o descripción
        'course_id',        // Relación con el curso (nullable)
        'module_id',        // Relación con el módulo (nullable si puede ser global)
        'lesson_id',        // Relación con la lección (nullable)
        'type',             // Tipo: quiz, examen, tarea, etc.
        'max_score',        // Puntaje máximo
        'passing_score',    // Puntaje mínimo para aprobar
        'max_attempts',     // Número máximo de intentos (null = ilimitados)
        'duration_minutes', // Duración máxima en minutos (null = sin límite)
        'show_feedback',    // Mostrar respuestas correctas después de evaluar
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'duration' => 'integer',  // Duración en minutos
        'max_score' => 'float',   // Puntaje máximo
        'passing_score' => 'float', // Puntaje mínimo para aprobar
        'max_attempts' => 'integer', // Número máximo de intentos
        'duration_minutes' => 'integer', // Duración en minutos
        'show_feedback' => 'boolean', // Mostrar feedback
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function isOwnedByTeam(?int $teamId): bool
    {
        return $this->course?->isOwnedByTeam($teamId) ?? false;
    }

    public function isVisibleToTeam(?int $teamId): bool
    {
        return $this->course?->isVisibleToTeam($teamId) ?? false;
    }

    public function scopeOwnedByTeam(Builder $query, ?int $teamId): Builder
    {
        return $query->whereHas('course', fn (Builder $courseQuery) => $courseQuery->ownedByTeam($teamId));
    }

    public function scopeVisibleToTeam(Builder $query, ?int $teamId): Builder
    {
        return $query->whereHas('course', fn (Builder $courseQuery) => $courseQuery->visibleToTeam($teamId));
    }

    /**
     * Verifica si la evaluación está disponible actualmente.
     */
    /* public function isAvailable(): bool
    {
        $now = now();
        return $this->active &&
            (!$this->start_at || $now->gte($this->start_at)) &&
            (!$this->end_at || $now->lte($this->end_at));
    } */
}
