<?php

namespace App\Models\Quality\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    /** @use HasFactory<\Database\Factories\Quality\Training\ProgressFactory> */
    use HasFactory;

    protected $fillable = [
        'enrollment_id',   // Relación con la inscripción
        'lesson_id',       // Relación con la lección
        'status',          // Estado: not_started, in_progress, completed
        'started_at',      // Fecha/hora de inicio de la lección
        'completed_at',    // Fecha/hora de finalización
        'last_accessed_at' // Último acceso a la lección
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];

    /* public function getProgressPercentageAttribute()
    {
        $totalLessons = $this->lesson->count();
        if ($totalLessons === 0) {
            return 0;
        }
        $completedLessons = $this->lessonsCompleted()->count();
        return ($completedLessons / $totalLessons) * 100;
    } */

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
