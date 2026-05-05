<?php

namespace App\Models\Quality\Improvement;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Verification extends Model
{
    protected $table = 'quality_improvement_verifications';

    protected $fillable = [
        'team_id',
        'plan_id',
        'verified_by',
        'result',
        'before_value',
        'after_value',
        'verified_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'before_value' => 'decimal:2',
            'after_value' => 'decimal:2',
            'verified_at' => 'datetime',
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

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

