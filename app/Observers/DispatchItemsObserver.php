<?php

namespace App\Observers;

use App\Models\DispatchItems;
use App\Models\Stock;

class DispatchItemsObserver
{
    public function created(DispatchItems $item): void
    {
        Stock::where([
            ['product_id',    $item->purchaseItem->product_id],
            ['central_batch_id',      $item->batch_id],
        ])->decrement('quantity', $item->quantity);
    }

    public function updating(DispatchItems $item): void
    {
        $original = $item->getOriginal();

        // 1) Si cambió lote o cantidad, repón el original
        Stock::where([
            ['product_id', $item->purchaseItem->product_id],
            ['central_batch_id',   $original['batch_id']],
        ])->increment('quantity', $original['quantity']);

        // 2) Y luego descuenta el nuevo
        Stock::where([
            ['product_id', $item->purchaseItem->product_id],
            ['central_batch_id',   $item->batch_id],
        ])->decrement('quantity', $item->quantity);
    }

    public function deleted(DispatchItems $item): void
    {
        // Si borras un despacho, devuelves el stock
        Stock::where([
            ['product_id', $item->purchaseItem->product_id],
            ['central_batch_id',   $item->batch_id],
        ])->increment('quantity', $item->quantity);
    }
}

