<?php

namespace App\Models\Quality\Documentation;

use App\Models\Document;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentDistribution extends Model
{
    protected $fillable = [
        'team_id',
        'document_id',
        'document_version_id',
        'scope_type',
        'scope_id',
        'required_read',
        'distributed_by',
        'distributed_at',
    ];

    protected function casts(): array
    {
        return [
            'required_read' => 'boolean',
            'distributed_at' => 'datetime',
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

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }
}

