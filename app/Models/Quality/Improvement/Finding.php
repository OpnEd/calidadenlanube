<?php

namespace App\Models\Quality\Improvement;

use App\Models\Process;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Finding extends Model
{
    protected $table = 'quality_improvement_findings';

    protected $fillable = [
        'team_id',
        'process_id',
        'reported_by',
        'source',
        'severity',
        'status',
        'title',
        'description',
        'detected_at',
    ];

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class, 'finding_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class, 'finding_id');
    }
}

