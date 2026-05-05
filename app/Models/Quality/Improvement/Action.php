<?php

namespace App\Models\Quality\Improvement;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Action extends Model
{
    protected $table = 'quality_improvement_actions';

    protected $fillable = [
        'team_id',
        'plan_id',
        'responsible_user_id',
        'title',
        'description',
        'status',
        'progress',
        'start_date',
        'due_date',
        'completed_at',
        'cost_estimated',
        'cost_real',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'cost_estimated' => 'decimal:2',
            'cost_real' => 'decimal:2',
            'progress' => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class, 'action_id');
    }
}

