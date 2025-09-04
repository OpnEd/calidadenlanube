<?php

namespace App\Models\Quality\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'duration',         // Duración en minutos
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'duration' => 'integer',  // Duración en minutos
        'max_score' => 'float',   // Puntaje máximo
        'passing_score' => 'float', // Puntaje mínimo para aprobar
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function attempts()
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
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
