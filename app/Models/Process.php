<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Process extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id', //FK opcional para procesos específicos del inquilino (nullable si es un proceso general)
        'process_type_id',
        'records',
        'code',
        'name',
        'description',
        //'role_id',
        'suppliers',
        'inputs',
        'procedures',
        'outputs',
        'clients',
    ];

    protected $casts = [
        'records' => 'array',
        'suppliers' => 'array',
        'inputs' => 'array',
        'procedures' => 'array',
        'outputs' => 'array',
        'clients' => 'array',
    ];

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function process_type(): BelongsTo
    {
        return $this->belongsTo(ProcessType::class);
    }

    public function records(): BelongsToMany
    {
        return $this->belongsToMany(Record::class);
    }

    /* public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    } */

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
