<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelVersion extends Model
{
    protected $fillable = [
        'team_id',
        'versionable_type',
        'versionable_id',
        'user_id',
        'changes',
        'snapshot',
        'comment',
    ];
    
    protected $casts = [
        'changes' => 'array',
        'snapshot' => 'array',
    ];

    public function versionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

