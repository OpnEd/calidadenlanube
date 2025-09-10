<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'central_batch_id',
        'quantity',
        'purchase_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
    ];

    public function sale_items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function central_batch()
    {
        return $this->belongsTo(CentralBatch::class);
    }
}
