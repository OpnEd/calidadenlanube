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

    public const TYPE_MULTIPLE_CHOICE_SINGLE = 'multiple_choice_single';
    public const TYPE_MULTIPLE_CHOICE_MULTIPLE = 'multiple_choice_multiple';
    public const TYPE_TRUE_FALSE = 'true_false';
    public const TYPE_FREE_TEXT = 'free_text';

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

    public function questionOptions(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function isRequired(): bool
    {
        return (bool) data_get($this->data, 'required', true);
    }

    public function isOptionBased(): bool
    {
        return in_array($this->type, [
            self::TYPE_MULTIPLE_CHOICE_SINGLE,
            self::TYPE_MULTIPLE_CHOICE_MULTIPLE,
            self::TYPE_TRUE_FALSE,
        ], true);
    }

    public function isMultipleChoiceSingle(): bool
    {
        return $this->type === self::TYPE_MULTIPLE_CHOICE_SINGLE;
    }

    public function isMultipleChoiceMultiple(): bool
    {
        return $this->type === self::TYPE_MULTIPLE_CHOICE_MULTIPLE;
    }

    public function isTrueFalse(): bool
    {
        return $this->type === self::TYPE_TRUE_FALSE;
    }

    public function isFreeText(): bool
    {
        return $this->type === self::TYPE_FREE_TEXT;
    }

    public function isAutoGradable(): bool
    {
        return ! $this->isFreeText();
    }
}
