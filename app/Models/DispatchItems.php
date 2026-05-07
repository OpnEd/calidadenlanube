<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DispatchItems extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'dispatch_id',
        'purchase_item_id',
        'batch_id',
        'quantity',
        'price',
        'total',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }
    
    public function batch(): BelongsTo
    {
        return $this->belongsTo(CentralBatch::class, 'batch_id');
    }

    public function central_batch(): BelongsTo
    {
        return $this->belongsTo(CentralBatch::class, 'batch_id');
    }
}
