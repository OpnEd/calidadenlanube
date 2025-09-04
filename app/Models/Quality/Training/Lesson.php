<?php

namespace App\Models\Quality\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lesson extends Model
{
    /** @use HasFactory<\Database\Factories\Quality\Training\LessonFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'objective',
        'description',
        'duration', // Duration in minutes
        'module_id',
        'order',
        'content',
        'video_url',
        'iframe',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'duration' => 'integer',
        'content' => 'array',
    ];

    /* public function getVideoUrlAttribute()
    {
        return $this->video_url ? asset('storage/' . $this->video_url) : null;
    } */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
    public function assessment(): HasOne
    {
        return $this->hasOne(Assessment::class);
    }
    public function getIsActiveAttribute()
    {
        return $this->active;
    }
    public function getDurationInHoursAttribute()
    {
        return $this->duration / 60; // Convert minutes to hours
    }
    public function getDurationInHoursFormattedAttribute()
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }
    /* public function getContentAttribute()
    {
        return $this->content;
    } */
    // Ejemplo avanzado: Devolver el objetivo en mayúsculas y con un resumen
    /* public function getObjectiveAttribute()
    {
        $objective = $this->attributes['objective'] ?? '';
        $summary = strlen($objective) > 50 ? substr($objective, 0, 47) . '...' : $objective;
        return [
            'original' => $objective,
            'uppercase' => strtoupper($objective),
            'summary' => $summary,
        ];
    } */

    /**
     * Obtener la siguiente lección del mismo módulo.
     */
    public function nextLesson()
    {
        return self::where('module_id', $this->module_id)
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }

    /**
     * Obtener la lección anterior del mismo módulo.
     */
    public function previousLesson()
    {
        return self::where('module_id', $this->module_id)
            ->where('order', '<', $this->order)
            ->orderByDesc('order')
            ->first();
    }

    /**
     * Obtener el curso al que pertenece la lección a través del módulo.
     */
    public function course()
    {
        return $this->module ? $this->module->course : null;
    }

    /**
     * Verificar si la lección tiene video.
     */
    public function hasVideo()
    {
        return !empty($this->video_url);
    }

    /**
     * Relación con las finalizaciones de lección por usuario.
     */
    public function completions()
    {
        return $this->hasMany(\App\Models\Quality\Training\LessonCompletion::class);
    }

    /**
     * Marcar la lección como completada por un usuario.
     */
    public function markAsCompletedBy($user)
    {
        return $this->completions()->firstOrCreate(['user_id' => $user->id]);
    }

}
