<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReception extends Model
{
    /** @use HasFactory<\Database\Factories\ProductReceptionFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_IN_PROGRESS = 'in progress';
    public const STATUS_DONE = 'done';

    protected $fillable = [
        'team_id',
        'user_id',
        'purchase_id',
        'invoice_id',
        'status',
        'reception_date',
        'observations',
        'data',
    ];

    protected $casts = [
        'status' => 'string',
        'reception_date' => 'datetime',
        'data' => 'array',
    ];

    public function isDone(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductReceptionItem::class);
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
}
