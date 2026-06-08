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
        'assessment_id',
        'enrollment_id',
        'lesson_id',
        'user_id',
        'score',
        'status',
        'started_at',
        'completed_at',
        'responses',
        'passed',
        'passed_at',
        'feedback',
    ];

    protected $casts = [
        'score' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'responses' => 'array',
        'passed' => 'boolean',
        'passed_at' => 'datetime',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function durationInMinutes(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }

        return null;
    }

    public function isPassed(): bool
    {
        return (bool) ($this->passed ?? false);
    }
}
