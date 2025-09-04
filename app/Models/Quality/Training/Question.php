<?php

namespace App\Models\Quality\Training;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'assessment_id',
        'question_text',
        'type',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function question_options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function user_answers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
