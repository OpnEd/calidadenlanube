<?php

namespace App\Observers;

use App\Models\SaleItem;
use Illuminate\Support\Facades\Log;

class SaleItemObserver
{
    /**
     * Handle the SaleItem "created" event.
     *
     * @param  \App\Models\SaleItem  $saleItem
     * @return void
     */
    public function created(SaleItem $saleItem): void
    {
        // Obtener el inventario asociado
        $inventory = $saleItem->inventory;

        if ($inventory) {
            // Descontar la cantidad vendida
            $inventory->decrement('quantity', $saleItem->quantity);
            //Log::info(sprintf("Inventory ID %d decremented by %d", $inventory->id, $saleItem->quantity));
        }
    }

    /**
     * Handle the SaleItem "deleted" event.
     * Si se elimina un detalle de venta, Restaurar inventario.
     *
     * @param  \App\Models\SaleItem  $saleItem
     * @return void
     */
    public function deleted(SaleItem $saleItem): void
    {
        $inventory = $saleItem->inventory;

        if ($inventory) {
            // Reponer la cantidad eliminada
            $inventory->increment('quantity', $saleItem->quantity);
            //Log::info(sprintf("Inventory ID %d incremented by %d", $inventory->id, $saleItem->quantity));
        }
    }

    // Otros métodos (updated, restored) pueden implementarse según necesidad
}
