<?php

namespace App\Models\Quality\Improvement;

use App\Models\Process;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $table = 'quality_improvement_plans';

    protected $fillable = [
        'team_id',
        'finding_id',
        'process_id',
        'owner_user_id',
        'code',
        'objective',
        'scope',
        'priority',
        'status',
        'start_date',
        'due_date',
        'baseline_value',
        'target_value',
        'expected_impact',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'closed_at' => 'datetime',
            'baseline_value' => 'decimal:2',
            'target_value' => 'decimal:2',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function finding(): BelongsTo
    {
        return $this->belongsTo(Finding::class, 'finding_id');
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class, 'plan_id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class, 'plan_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class, 'plan_id');
    }
}

