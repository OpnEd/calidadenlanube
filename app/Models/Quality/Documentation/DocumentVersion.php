<?php

namespace App\Models\Quality\Documentation;

use App\Models\Document;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentVersion extends Model
{
    protected $fillable = [
        'team_id',
        'document_id',
        'created_by',
        'version',
        'status',
        'is_current',
        'change_summary',
        'body',
        'file_path',
        'effective_at',
        'expires_at',
        'reviewed_at',
        'approved_at',
        'published_at',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'effective_at' => 'date',
            'expires_at' => 'date',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'data' => 'array',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(DocumentReview::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(DocumentDistribution::class);
    }

    public function acknowledgments(): HasMany
    {
        return $this->hasMany(DocumentAcknowledgment::class);
    }
}

