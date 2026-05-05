<?php

namespace App\Models\Quality\Documentation;

use App\Models\Document;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAcknowledgment extends Model
{
    protected $fillable = [
        'team_id',
        'document_id',
        'document_version_id',
        'user_id',
        'required',
        'status',
        'due_at',
        'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'due_at' => 'date',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'document_version_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

