<?php

namespace App\Models\Quality\Improvement;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evidence extends Model
{
    protected $table = 'quality_improvement_evidences';

    protected $fillable = [
        'team_id',
        'finding_id',
        'plan_id',
        'action_id',
        'uploaded_by',
        'file_path',
        'description',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function finding(): BelongsTo
    {
        return $this->belongsTo(Finding::class, 'finding_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class, 'action_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

