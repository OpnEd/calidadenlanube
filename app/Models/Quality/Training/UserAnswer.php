<?php

namespace App\Models\Quality\Training;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    protected $fillable = [
        'user_id',
        'question_id',
        'question_option_id',
        'assessment_attempt_id',
    ];
}
