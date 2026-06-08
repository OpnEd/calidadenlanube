<?php

namespace App\Models\Quality\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentLesson extends Model
{
    use HasFactory;

    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CONSUMED = 'consumed';
    public const STATUS_PASSED = 'passed';

    protected $table = 'enrollment_lesson';

    protected $fillable = [
        'enrollment_id',
        'lesson_id',
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
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'consumed_at' => 'datetime',
        'passed_at' => 'datetime',
        'certificate_issued_at' => 'datetime',
        'passed' => 'boolean',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function approvedAttempt(): BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class, 'approved_attempt_id');
    }

    public function isResolved(): bool
    {
        return match ($this->lesson?->completion_mode) {
            Lesson::COMPLETION_MODE_CONSUMPTION_ONLY => $this->status === self::STATUS_CONSUMED,
            default => $this->status === self::STATUS_PASSED,
        };
    }
}
