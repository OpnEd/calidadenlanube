<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'sale_item_id',
        'dispatch_item_id',
        'batch_id',
        'central_batch_id',
        'due_date',
        'quantity',
        'price',
        'total'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'due_date' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function batchs(): BelongsTo
    {
        return $this->batch();
    }

    public function centralBatch(): BelongsTo
    {
        return $this->belongsTo(CentralBatch::class, 'central_batch_id');
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class, 'sale_item_id');
    }

    public function saleItems(): BelongsTo
    {
        return $this->saleItem();
    }

    public function dispatchItem(): BelongsTo
    {
        return $this->belongsTo(DispatchItems::class, 'dispatch_item_id');
    }

    public function dispatchItems(): BelongsTo
    {
        return $this->dispatchItem();
    }
}
