<?php

namespace App\Models\Quality;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    protected $fillable = [
        'team_id',
        'document_id',
        'user_id',
        'changes',
        'comment',
    ];
    protected $casts = [
        'changes' => 'array',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function document() { return $this->belongsTo(Document::class); }
}
