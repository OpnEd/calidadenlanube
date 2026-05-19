<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dispatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'team_id',
        'user_id',
        'dispatched_at',
        'data',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'data' => 'array',
    ];

    // Relación uno a uno con factura
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DispatchItems::class);
    }
}
