<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CentralBatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'manufacturer_id',
        'sanitary_registry_id',
        'manufacturing_date',
        'expiration_date',
        'data',
    ];

    protected $casts = [
        'manufacturing_date' => 'datetime',
        'expiration_date' => 'datetime',
        'data' => 'array',
    ];

    public function dispatchItems(): HasMany
    {
        return $this->hasMany(DispatchItems::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function product_reception_items(): HasMany
    {
        return $this->hasMany(ProductReceptionItem::class);
    }

    public function sanitary_registry(): BelongsTo
    {
        return $this->belongsTo(SanitaryRegistry::class);
    }
}
