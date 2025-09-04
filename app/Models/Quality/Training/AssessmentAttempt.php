<?php

namespace App\Models\Quality\Training;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentAttempt extends Model
{
    /** @use HasFactory<\Database\Factories\Quality\Training\AssessmentAttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'assessment_id',    // Relación con la evaluación
        'user_id',          // Relación con el usuario que intenta
        'score',            // Puntaje obtenido
        'status',           // Estado: in_progress, completed, etc.
        'started_at',       // Fecha/hora de inicio del intento
        'completed_at',     // Fecha/hora de finalización
        'responses',        // Respuestas dadas (puede ser JSON)
        'passed',           // Indica si el intento fue aprobado
        'feedback',
    ];

    protected $casts = [
        'score' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'responses' => 'array',
        'passed' => 'boolean',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }

    /**
     * Verifica si el intento está completado.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Duración del intento en minutos.
     */
    public function durationInMinutes(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }
        return null;
    }

    public function isPassed(): bool
    {
        return $this->score >= $this->assessment->passing_score;
    }
}
