<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $fillable = [
        'team_id',
        'product_id',
        //'bar_code',
        'product_name',
        'batch_id',
        //'batch_code',
        'quantity',
        'purchase_price',
        //'sale_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        //'sale_price' => 'decimal:2',
    ];
    
    public function anesthesiaSheetItems(): HasMany
    {
        return $this->hasMany(AnesthesiaSheetItem::class);
    }

    public function sale_items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
